<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Enums\GalleryMediaType;
use App\Filament\Tables\Columns\AdminPreviewImageColumn;
use App\Support\Staff\StaffContentAccess;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                AdminPreviewImageColumn::make('image')
                    ->label('Preview')
                    ->disk('public')
                    ->height(48)
                    ->square()
                    ->getStateUsing(fn ($record) => $record->thumbnail ?: $record->image),
                TextColumn::make('media_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof GalleryMediaType
                        ? $state->label()
                        : (string) $state)
                    ->color(fn ($state): string => ($state instanceof GalleryMediaType
                        ? $state->value
                        : (string) $state) === 'video' ? 'warning' : 'gray'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Gallery Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('featured')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Featured' : 'Standard')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray'),
                TextColumn::make('homepage_featured')
                    ->label('Homepage')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('media_type')
                    ->label('Media Type')
                    ->options(GalleryMediaType::options()),
                SelectFilter::make('gallery_category_id')
                    ->label('Gallery Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('services')
                    ->label('Service')
                    ->relationship('services', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('status')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All'),
                TernaryFilter::make('featured')
                    ->boolean()
                    ->trueLabel('Featured')
                    ->falseLabel('Not featured')
                    ->placeholder('All'),
                TernaryFilter::make('homepage_featured')
                    ->label('Homepage')
                    ->boolean()
                    ->trueLabel('On homepage')
                    ->falseLabel('Not on homepage')
                    ->placeholder('All'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                    RestoreBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                    ForceDeleteBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                ]),
            ])
            ->emptyStateHeading('No gallery items yet')
            ->emptyStateDescription('Upload photos to showcase your events.')
            ->emptyStateIcon(Heroicon::OutlinedSquares2x2);
    }
}
