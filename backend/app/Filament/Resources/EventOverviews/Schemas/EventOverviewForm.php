<?php

namespace App\Filament\Resources\EventOverviews\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class EventOverviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Event Overview')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('caption')
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        TextInput::make('link_url')
                                            ->label('Link URL')
                                            ->maxLength(255)
                                            ->helperText('Optional path or URL (e.g. /services).')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        FileUpload::make('image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('event-overviews')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->required()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                    ]),
                            ]),

                        Tab::make('Publish')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        ...WebsitePublishFields::statusAndSort(withHomepage: true),
                                        Toggle::make('featured')
                                            ->label('Featured')
                                            ->default(false)
                                            ->inline(false),
                                        ...WebsitePublishFields::schedule(),
                                    ]),
                                Section::make('History')
                                    ->columns(2)
                                    ->schema(WebsitePublishFields::audit())
                                    ->collapsed(),
                            ]),
                    ]),
            ]);
    }
}
