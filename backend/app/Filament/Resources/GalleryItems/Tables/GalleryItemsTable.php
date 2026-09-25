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
    public static function configure(Table $table, bool $staffMode = false): Table
    {
        $columns = [
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
        ];

        if ($staffMode) {
            $columns[] = TextColumn::make('moderation_status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => \App\Enums\ContentModerationStatus::tryFrom((string) $state)?->label() ?? 'Draft');
            $columns[] = TextColumn::make('moderation_notes')
                ->label('Rejection reason')
                ->wrap()
                ->limit(80)
                ->placeholder('—');
        } else {
            $columns[] = TextColumn::make('slug')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true);
            $columns[] = TextColumn::make('featured')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Featured' : 'Standard')
                ->color(fn (bool $state): string => $state ? 'warning' : 'gray');
            $columns[] = TextColumn::make('homepage_featured')
                ->label('Homepage')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                ->color(fn (bool $state): string => $state ? 'success' : 'gray');
            $columns[] = TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                ->color(fn (bool $state): string => $state ? 'success' : 'danger');
            $columns[] = TextColumn::make('sort_order')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true);
        }

        $columns[] = TextColumn::make('updated_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $table = $table
            ->columns($columns)
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->emptyStateHeading($staffMode ? 'No posts yet' : 'No gallery items yet')
            ->emptyStateDescription($staffMode ? 'Create a post, save a draft, then submit it for review.' : 'Upload photos to showcase your events.')
            ->emptyStateIcon(Heroicon::OutlinedSquares2x2);

        if (! $staffMode) {
            $table = $table
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
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                        RestoreBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                        ForceDeleteBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                    ]),
                ]);
        }

        return $table;
    }
}

