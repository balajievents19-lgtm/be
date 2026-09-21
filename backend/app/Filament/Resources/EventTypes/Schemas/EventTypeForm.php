<?php

namespace App\Filament\Resources\EventTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Optional. Auto-generated from name when left blank.'),
                        TextInput::make('icon')
                            ->label('Icon class (optional)')
                            ->maxLength(100)
                            ->helperText('Example: icon-wedding — leave blank to use the default form icon.'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('status')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive types are hidden from the public search form.'),
                    ]),
            ]);
    }
}
