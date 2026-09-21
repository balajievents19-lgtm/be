<?php

namespace App\Filament\Resources\ExternalMedia\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ExternalMediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('External Media')
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
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Select::make('media_type')
                                            ->label('Media type')
                                            ->options([
                                                'video' => 'Video',
                                                'social_post' => 'Social post',
                                                'external' => 'External media',
                                            ])
                                            ->required()
                                            ->native(false),
                                        Select::make('provider')
                                            ->label('Provider')
                                            ->options([
                                                'youtube' => 'YouTube',
                                                'instagram' => 'Instagram',
                                                'facebook' => 'Facebook',
                                                'google_drive' => 'Google Drive',
                                                'vimeo' => 'Vimeo',
                                                'other' => 'Other URL',
                                            ])
                                            ->required()
                                            ->native(false)
                                            ->helperText('Do not upload video files. Store the share/watch URL only.'),
                                        TextInput::make('url')
                                            ->label('URL')
                                            ->required()
                                            ->url()
                                            ->maxLength(2048)
                                            ->columnSpanFull()
                                            ->helperText('https only. YouTube/Vimeo embed when valid; Instagram/Facebook/Drive open as safe links.'),
                                        Textarea::make('description')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        WebsitePublishFields::previewPlaceholder('/media'),
                                    ]),
                            ]),
                        Tab::make('Thumbnail')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Optional thumbnail (image only)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('external-media/thumbnails')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->helperText('Optional cover image. Never upload the video file itself.'),
                                    ]),
                            ]),
                        Tab::make('Publish')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        ...WebsitePublishFields::statusAndSort(withHomepage: true),
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
