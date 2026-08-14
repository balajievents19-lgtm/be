<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Slide')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('subtitle')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('button_text')
                                            ->maxLength(100),
                                        TextInput::make('button_url')
                                            ->label('Button URL')
                                            ->maxLength(255)
                                            ->nullable(),
                                        TextInput::make('video_url')
                                            ->label('Video URL')
                                            ->url()
                                            ->maxLength(500)
                                            ->nullable()
                                            ->columnSpanFull(),
                                        WebsitePublishFields::previewPlaceholder('/'),
                                    ]),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('desktop_image')
                                            ->label('Desktop Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('hero-slides/desktop')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->required()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                        FileUpload::make('mobile_image')
                                            ->label('Mobile Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('hero-slides/mobile')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->nullable()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                    ]),
                            ]),

                        Tab::make('Publish')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('overlay_opacity')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(40)
                                            ->suffix('%')
                                            ->required(),
                                        Select::make('text_alignment')
                                            ->options([
                                                'left' => 'Left',
                                                'center' => 'Center',
                                                'right' => 'Right',
                                            ])
                                            ->default('center')
                                            ->required()
                                            ->native(false),
                                        ...WebsitePublishFields::statusAndSort(),
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
