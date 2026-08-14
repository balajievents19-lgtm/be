<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->placeholder('No roles')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => Auth::user()?->can('delete', $record) ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete')
                        ->action(function (Collection $records): void {
                            $deleted = 0;
                            $skipped = 0;

                            $records->each(function (User $record) use (&$deleted, &$skipped): void {
                                $actor = Auth::user();

                                if ($actor === null || ! $actor->can('delete', $record)) {
                                    $skipped++;

                                    return;
                                }

                                $record->delete();
                                $deleted++;
                            });

                            if ($skipped > 0) {
                                Notification::make()
                                    ->title('Some users were not deleted')
                                    ->body("Deleted {$deleted}. Skipped {$skipped} (self or last Super Admin).")
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Users deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Create an admin user to access the control panel.')
            ->emptyStateIcon(Heroicon::OutlinedUsers);
    }
}
