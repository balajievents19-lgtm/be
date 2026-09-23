<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use App\Filament\Tables\Columns\AdminPreviewImageColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                AdminPreviewImageColumn::make('desktop_image')
                    ->label('Desktop')
                    ->disk('public')
                    ->height(48)
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('button_text')
                    ->label('Button')
                    ->toggleable(),
                TextColumn::make('text_alignment')
                    ->badge()
                    ->sortable(),
                TextColumn::make('overlay_opacity')
                    ->label('Overlay')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('status')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No slides yet')
            ->emptyStateDescription('Add homepage slider images for the website hero.')
            ->emptyStateIcon(Heroicon::OutlinedPhoto);
    }
}
