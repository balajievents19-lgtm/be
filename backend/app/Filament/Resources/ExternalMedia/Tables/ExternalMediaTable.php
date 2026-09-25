<?php

namespace App\Filament\Resources\ExternalMedia\Tables;

use App\Filament\Tables\Columns\AdminPreviewImageColumn;
use App\Support\Staff\StaffContentAccess;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExternalMediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                AdminPreviewImageColumn::make('thumbnail')
                    ->label('Thumb')
                    ->disk('public')
                    ->height(40)
                    ->square()
                    ->defaultImageUrl(null),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('provider')
                    ->badge()
                    ->sortable(),
                TextColumn::make('media_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('url')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('status')
                    ->label('Published')
                    ->boolean(),
                IconColumn::make('homepage_featured')
                    ->label('Home')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('provider')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'google_drive' => 'Google Drive',
                        'vimeo' => 'Vimeo',
                        'other' => 'Other',
                    ]),
                SelectFilter::make('media_type')
                    ->options([
                        'video' => 'Video',
                        'social_post' => 'Social post',
                        'external' => 'External',
                    ]),
                TernaryFilter::make('status')->label('Published'),
                TernaryFilter::make('homepage_featured')->label('Homepage'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                    ForceDeleteBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                    RestoreBulkAction::make()->visible(fn (): bool => StaffContentAccess::canDeleteContent(Auth::user())),
                ]),
            ]);
    }
}
