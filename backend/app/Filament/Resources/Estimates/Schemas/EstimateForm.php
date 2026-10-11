<?php

namespace App\Filament\Resources\Estimates\Schemas;

use App\Enums\EstimateRateBasis;
use App\Enums\EstimateStatus;
use App\Filament\Resources\EstimateCatalogueItems\Schemas\EstimateCatalogueItemForm;
use App\Models\Estimate;
use App\Models\EstimateCatalogueItem;
use App\Support\Estimates\EstimateCalculator;
use App\Support\Estimates\EstimateCatalogueWriter;
use App\Support\Rbac\AdminModules;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EstimateForm
{
    public static function configure(Schema $schema): Schema
    {
        $showCosting = Auth::user()?->hasRole(AdminModules::ROLE_SUPER_ADMIN) ?? false;

        return $schema
            ->components([
                Tabs::make('Estimate')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Customer & event')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Estimate number')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder('Assigned on save (BRE-YYYY-NNNN)'),
                                        Select::make('status')
                                            ->options(EstimateStatus::options())
                                            ->default(EstimateStatus::Draft->value)
                                            ->required()
                                            ->native(false),
                                        DatePicker::make('estimate_date')
                                            ->required()
                                            ->default(now())
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set): void {
                                                if (blank($state)) {
                                                    return;
                                                }
                                                $set('valid_until', Estimate::defaultValidUntil(
                                                    Carbon::parse($state)
                                                )->toDateString());
                                            }),
                                        DatePicker::make('valid_until')
                                            ->required()
                                            ->default(Estimate::defaultValidUntil())
                                            ->helperText('Always Estimate Date + 5 calendar days.'),
                                        TextInput::make('customer_name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('customer_mobile')
                                            ->required()
                                            ->tel()
                                            ->maxLength(30),
                                        Select::make('event_type_id')
                                            ->label('Event / function')
                                            ->relationship('eventType', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->native(false),
                                        TextInput::make('event_name')
                                            ->label('Event name')
                                            ->maxLength(255),
                                        TextInput::make('event_dates')
                                            ->label('Event dates')
                                            ->maxLength(255),
                                        TextInput::make('venue')->maxLength(255),
                                        TextInput::make('city')->maxLength(255),
                                    ]),
                            ]),
                        Tab::make('Items')
                            ->schema([
                                Repeater::make('lines')
                                    ->label('Items')
                                    ->minItems(1)
                                    ->defaultItems(1)
                                    ->schema(self::lineSchema($showCosting))
                                    ->columns(12)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add item')
                                    ->reorderable()
                                    ->live(),
                            ]),
                        Tab::make('Totals & terms')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('discount_amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('INR')
                                            ->default(0),
                                        TextInput::make('additional_charges')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('INR')
                                            ->default(0),
                                        TextInput::make('tax_rate')
                                            ->label('Tax % (only if applicable)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->suffix('%')
                                            ->default(0)
                                            ->helperText('Leave 0 if tax is not configured or not applicable. Do not invent GST.'),
                                        TextInput::make('tax_label')
                                            ->maxLength(50)
                                            ->placeholder('e.g. GST — only if legally applicable'),
                                        TextInput::make('advance_amount')
                                            ->label('Advance / booking amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('INR')
                                            ->default(0),
                                        Textarea::make('payment_terms')->rows(3),
                                        Textarea::make('payment_schedule')->rows(3),
                                        Textarea::make('notes')->rows(3),
                                        Textarea::make('special_notes')
                                            ->label('Special notes / scope exclusions')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function lineSchema(bool $showCosting): array
    {
        return [
            Select::make('catalogue_item_id')
                ->label('Item / Description')
                ->options(fn () => EstimateCatalogueItem::query()->active()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->columnSpan(4)
                ->createOptionForm(EstimateCatalogueItemForm::inlineComponents())
                ->createOptionUsing(function (array $data): int {
                    return app(EstimateCatalogueWriter::class)->save($data)->id;
                })
                ->helperText('Search the catalogue or use + Add new item.')
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if (blank($state)) {
                        return;
                    }
                    $item = EstimateCatalogueItem::query()->find($state);
                    if (! $item) {
                        return;
                    }
                    $set('name', $item->name);
                    $set('description', $item->description);
                    $set('rate', $item->default_rate);
                    $set('rate_basis', $item->rate_basis->value);
                    $set('actual_cost_rate', $item->actual_cost_rate);
                    if ($item->rate_basis === EstimateRateBasis::PerDay) {
                        $set('days', $get('days') ?: '1');
                    } else {
                        $set('days', null);
                    }
                    self::recalculateLine($get, $set);
                }),
            TextInput::make('name')
                ->label('Printed name')
                ->required()
                ->maxLength(255)
                ->columnSpan(4),
            TextInput::make('qty')
                ->numeric()
                ->required()
                ->minValue(0)
                ->default(1)
                ->live()
                ->columnSpan(1)
                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set)),
            TextInput::make('rate')
                ->numeric()
                ->required()
                ->minValue(0)
                ->live()
                ->columnSpan(1)
                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set)),
            Select::make('rate_basis')
                ->label('Basis')
                ->options(EstimateRateBasis::options())
                ->required()
                ->native(false)
                ->live()
                ->columnSpan(1)
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if ($state === EstimateRateBasis::OneTime->value) {
                        $set('days', null);
                    } elseif (blank($get('days'))) {
                        $set('days', '1');
                    }
                    self::recalculateLine($get, $set);
                }),
            TextInput::make('days')
                ->numeric()
                ->minValue(0)
                ->live()
                ->columnSpan(1)
                ->visible(fn (Get $get): bool => $get('rate_basis') === EstimateRateBasis::PerDay->value)
                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set)),
            TextInput::make('amount')
                ->disabled()
                ->dehydrated()
                ->numeric()
                ->columnSpan(2)
                ->helperText('Qty × Rate × Days (Per Day) or Qty × Rate (One-Time).'),
            Textarea::make('description')
                ->rows(2)
                ->columnSpan(12),
            TextInput::make('actual_cost_rate')
                ->label('Actual cost rate')
                ->numeric()
                ->minValue(0)
                ->visible($showCosting)
                ->live()
                ->columnSpan(2)
                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateLine($get, $set)),
        ];
    }

    private static function recalculateLine(Get $get, Set $set): void
    {
        try {
            $amount = EstimateCalculator::lineAmount(
                (string) ($get('qty') ?: 0),
                (string) ($get('rate') ?: 0),
                $get('days'),
                (string) ($get('rate_basis') ?: EstimateRateBasis::OneTime->value),
            );
            $set('amount', $amount);
        } catch (ValidationException|\InvalidArgumentException) {
            $set('amount', '0.00');
        }
    }
}
