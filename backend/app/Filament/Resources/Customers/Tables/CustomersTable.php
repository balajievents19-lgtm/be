<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('Mobile')->searchable(),
                IconColumn::make('email_verified_at')
                    ->label('Email verified')
                    ->boolean(),
                TextColumn::make('socialAccounts.provider')
                    ->label('Social')
                    ->badge()
                    ->separator(','),
                TextColumn::make('last_login_at')
                    ->label('Last login')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc');
    }
}
