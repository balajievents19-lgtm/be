<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Gallery Item')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('gallery_category_id')
                                            ->label('Gallery Category')
                                            ->relationship(
                                                name: 'category',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->ordered(),
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        CheckboxList::make('services')
                                            ->label('Show this photo in Services')
                                            ->relationship(
                                                name: 'services',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->active()->ordered(),
                                            )
                                            ->searchable()
                                            ->bulkToggleable()
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->helperText('The photo is uploaded once. It will appear in this Gallery Category and on every selected Service page.'),
                                        TextInput::make('title')
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
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash()
                                            ->helperText('Auto-generated from title; editable.'),
                                        WebsitePublishFields::previewPlaceholder('/gallery'),
                                        Textarea::make('description')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Image (public preview; original is secured privately)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('gallery/images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->required()
                                            ->maxSize(5120)
                                            ->helperText('On save, the original is moved to private storage. Public gallery shows a safe preview.')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail')
                                            ->image()
                                            ->disk('public')
                                            ->directory('gallery/thumbnails')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                        TextInput::make('alt_text')
                                            ->label('Alt Text')
                                            ->maxLength(255),
                                        TextInput::make('caption')
                                            ->maxLength(255),
                                        TextInput::make('youtube_url')
                                            ->label('YouTube URL (external — no video upload)')
                                            ->url()
                                            ->maxLength(255)
                                            ->helperText('Prefer Gallery → External Media for social/Drive links. Do not upload video files.'),
                                        TextInput::make('vimeo_url')
                                            ->label('Vimeo URL (external — no video upload)')
                                            ->url()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        FileUpload::make('opengraph_image')
                                            ->label('OpenGraph Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('gallery/seo')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048)
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
