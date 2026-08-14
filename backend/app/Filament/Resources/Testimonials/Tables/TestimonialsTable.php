<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Enums\TestimonialType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->disk('public')
                    ->height(40)
                    ->circular()
                    ->toggleable(),
                ImageColumn::make('image')
                    ->disk('public')
                    ->height(40)
                    ->square()
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (TestimonialType|string|null $state): string => $state instanceof TestimonialType
                        ? $state->label()
                        : (TestimonialType::tryFrom((string) $state)?->label() ?? (string) $state)),
                TextColumn::make('homepage_featured')
                    ->label('Homepage')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('type')->options(TestimonialType::options()),
                TernaryFilter::make('status')->label('Active')->boolean(),
                TernaryFilter::make('homepage_featured')->label('Homepage')->boolean(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No testimonials yet')
            ->emptyStateDescription('Add testimonials and success stories for the homepage.')
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleLeftRight);
    }
}
