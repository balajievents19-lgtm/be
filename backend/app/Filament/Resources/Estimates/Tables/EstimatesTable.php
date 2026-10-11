<?php

namespace App\Filament\Resources\Estimates\Tables;

use App\Enums\EstimateStatus;
use App\Filament\Resources\Estimates\EstimateResource;
use App\Models\Estimate;
use App\Support\Estimates\EstimatePersister;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EstimatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('customer_name')->searchable()->sortable(),
                TextColumn::make('customer_mobile')->searchable()->toggleable(),
                TextColumn::make('event_name')->searchable()->placeholder('—'),
                TextColumn::make('estimate_date')->date()->sortable(),
                TextColumn::make('valid_until')->date()->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (Estimate $record): string {
                        return $record->displayStatus()->label();
                    })
                    ->color(fn (Estimate $record): string => $record->displayStatus()->color()),
                TextColumn::make('grand_total')->money('INR')->sortable(),
                TextColumn::make('updated_at')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(EstimateStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('pdf')
                    ->label('PDF')
                    ->url(fn (Estimate $record): string => route('admin.estimates.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('duplicate')
                    ->requiresConfirmation()
                    ->action(function (Estimate $record): void {
                        $copy = app(EstimatePersister::class)->duplicate($record, Auth::user());
                        redirect(EstimateResource::getUrl('edit', ['record' => $copy]));
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
