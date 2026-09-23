<?php

namespace App\Filament\Resources\NavigationItems\Tables;

use App\Filament\Tables\Columns\AdminPreviewImageColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NavigationItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                AdminPreviewImageColumn::make('image')
                    ->disk('public')
                    ->height(40)
                    ->toggleable(),
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('Link')
                    ->searchable()
                    ->limit(40),
                IconColumn::make('show_on_header')
                    ->label('Header')
                    ->boolean(),
                IconColumn::make('show_on_footer')
                    ->label('Footer')
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                IconColumn::make('status')
                    ->label('Published')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('publish_at')
                    ->label('Publish From')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unpublish_at')
                    ->label('Publish Until')
                    ->dateTime()
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
                TernaryFilter::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->trueLabel('Visible only')
                    ->falseLabel('Hidden only')
                    ->placeholder('All'),
                TernaryFilter::make('show_on_header')
                    ->label('Header')
                    ->boolean()
                    ->trueLabel('Header only')
                    ->falseLabel('Not in header')
                    ->placeholder('All'),
                TernaryFilter::make('show_on_footer')
                    ->label('Footer')
                    ->boolean()
                    ->trueLabel('Footer only')
                    ->falseLabel('Not in footer')
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
            ->emptyStateHeading('No navigation items yet')
            ->emptyStateDescription('Create header and footer navigation links from CMS.')
            ->emptyStateIcon(Heroicon::OutlinedBars3);
    }
}
