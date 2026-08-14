<?php

namespace App\Filament\Resources\OfficeLocations\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class OfficeLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Office')->columnSpanFull()->tabs([
                Tab::make('General')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        Toggle::make('is_primary')->label('Primary Office')->default(false)->inline(false),
                        Textarea::make('address')->required()->rows(3)->columnSpanFull(),
                        TextInput::make('city')->maxLength(100),
                        TextInput::make('state')->maxLength(100),
                        TextInput::make('pincode')->label('PIN Code')->maxLength(20),
                        TextInput::make('phone')->maxLength(50),
                        TextInput::make('email')->email()->maxLength(255),
                        Textarea::make('map_embed')->label('Google Map Embed')->rows(4)->columnSpanFull(),
                        TextInput::make('latitude')->maxLength(50),
                        TextInput::make('longitude')->maxLength(50),
                    ]),
                ]),
                Tab::make('Publish')->schema([
                    Section::make()->columns(2)->schema([
                        ...WebsitePublishFields::statusAndSort(),
                        Toggle::make('is_visible')->label('Visible')->default(true)->inline(false),
                        ...WebsitePublishFields::schedule(),
                    ]),
                    Section::make('History')->columns(2)->schema(WebsitePublishFields::audit())->collapsed(),
                ]),
            ]),
        ]);
    }
}
