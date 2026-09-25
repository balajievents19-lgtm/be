<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\Rbac\AdminUserSecurity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->rule(Password::defaults())
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->confirmed()
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Leave blank to keep the current password.'
                                : null),
                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' || filled($get('password')))
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Required only when setting a new password.'
                                : null),
                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->visible(fn (): bool => AdminUserSecurity::canManageRoles(Auth::user()))
                            ->disabled(fn (): bool => ! AdminUserSecurity::canManageRoles(Auth::user()))
                            ->helperText('Only Super Admin can assign or change roles. Roles describe who the user is. Staff Access & Permissions below controls Services and Gallery Categories.')
                            ->columnSpanFull(),
                    ]),
                ...UserContentAccessFields::sections(),
            ]);
    }
}
