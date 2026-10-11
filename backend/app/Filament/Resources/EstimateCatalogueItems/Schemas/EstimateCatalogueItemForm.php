<?php

namespace App\Filament\Resources\EstimateCatalogueItems\Schemas;

use App\Enums\EstimateRateBasis;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EstimateCatalogueItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Item / service name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('default_rate')
                            ->label('Default rate')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('INR'),
                        Select::make('rate_basis')
                            ->label('Rate basis')
                            ->options(EstimateRateBasis::options())
                            ->required()
                            ->native(false),
                        TextInput::make('actual_cost_rate')
                            ->label('Actual cost rate (internal)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('INR')
                            ->helperText('Visible only to authorized admins. Never printed on the customer estimate.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }

    /**
     * Compact fields used by the estimate line “+ Add New Item” action.
     *
     * @return list<Component>
     */
    public static function inlineComponents(): array
    {
        return [
            TextInput::make('name')->required()->maxLength(255),
            Textarea::make('description')->rows(2),
            TextInput::make('default_rate')->numeric()->required()->minValue(0)->prefix('INR'),
            Select::make('rate_basis')
                ->options(EstimateRateBasis::options())
                ->required()
                ->native(false)
                ->default(EstimateRateBasis::PerDay->value),
            TextInput::make('actual_cost_rate')->numeric()->minValue(0)->prefix('INR')->label('Actual cost rate'),
            Toggle::make('is_active')->default(true),
        ];
    }
}
