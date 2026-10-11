<?php

namespace App\Filament\Resources\Estimates\Schemas;

use App\Enums\EstimateExpenseType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class EstimateCostingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.resources.estimates.pages.costing-summary')
                    ->columnSpanFull(),
                Repeater::make('lines')
                    ->label('Item actual cost rates')
                    ->schema([
                        Hidden::make('id'),
                        TextInput::make('name')->disabled()->dehydrated(),
                        TextInput::make('qty')->disabled()->dehydrated(),
                        TextInput::make('rate')->label('Customer rate')->disabled()->dehydrated(),
                        TextInput::make('days')->disabled()->dehydrated(),
                        TextInput::make('rate_basis')->disabled()->dehydrated(),
                        TextInput::make('amount')->label('Customer amount')->disabled()->dehydrated(),
                        TextInput::make('actual_cost_rate')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->prefix('INR'),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columns(7)
                    ->columnSpanFull(),
                Section::make('Additional direct event expenses')
                    ->schema([
                        Repeater::make('expenses')
                            ->schema([
                                Select::make('type')
                                    ->options(EstimateExpenseType::options())
                                    ->required()
                                    ->native(false),
                                TextInput::make('description')->required()->maxLength(255),
                                TextInput::make('amount')->numeric()->minValue(0)->required()->prefix('INR'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add expense')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
