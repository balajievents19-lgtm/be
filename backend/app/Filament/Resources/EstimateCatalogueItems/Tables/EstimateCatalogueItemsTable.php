<?php

namespace App\Filament\Resources\EstimateCatalogueItems\Tables;

use App\Enums\EstimateRateBasis;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EstimateCatalogueItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('default_rate')->money('INR')->sortable(),
                TextColumn::make('rate_basis')
                    ->formatStateUsing(fn (EstimateRateBasis|string|null $state): string => $state instanceof EstimateRateBasis
                        ? $state->label()
                        : (EstimateRateBasis::tryFrom((string) $state)?->label() ?? (string) $state)),
                TextColumn::make('actual_cost_rate')
                    ->label('Actual cost rate')
                    ->money('INR')
                    ->toggleable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }
}
