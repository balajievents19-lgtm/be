<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Enums\NewsletterSubscriberStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('last_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (NewsletterSubscriberStatus|string|null $state): string => $state instanceof NewsletterSubscriberStatus
                        ? $state->label()
                        : (NewsletterSubscriberStatus::tryFrom((string) $state)?->label() ?? (string) $state))
                    ->color(fn (NewsletterSubscriberStatus|string|null $state): string => $state instanceof NewsletterSubscriberStatus
                        ? $state->color()
                        : (NewsletterSubscriberStatus::tryFrom((string) $state)?->color() ?? 'gray')),
                TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(NewsletterSubscriberStatus::options()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No newsletter subscribers')
            ->emptyStateDescription('Subscribers from the website newsletter form will appear here.')
            ->emptyStateIcon(Heroicon::OutlinedEnvelope);
    }
}
