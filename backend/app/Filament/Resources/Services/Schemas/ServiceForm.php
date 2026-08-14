<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Service')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('service_category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->native(false),
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
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash()
                                            ->helperText('Auto-generated from name; editable.'),
                                        WebsitePublishFields::previewPlaceholder('/services'),
                                        Textarea::make('short_description')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                        RichEditor::make('full_description')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'h2',
                                                'h3',
                                                'undo',
                                                'redo',
                                            ]),
                                        TextInput::make('icon')
                                            ->label('Icon')
                                            ->helperText('Icon class (e.g. icon-caterers, icon-camera).')
                                            ->maxLength(100),
                                    ]),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('featured_image')
                                            ->label('Featured Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('services/featured')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->required()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                        FileUpload::make('banner_image')
                                            ->label('Banner Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('services/banners')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                                        FileUpload::make('gallery_images')
                                            ->label('Gallery Images')
                                            ->image()
                                            ->multiple()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory('services/gallery')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->maxFiles(20)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->columnSpanFull(),
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
                                        Textarea::make('seo_keywords')
                                            ->label('SEO Keywords')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        FileUpload::make('opengraph_image')
                                            ->label('OpenGraph Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('services/seo')
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
                                        ...WebsitePublishFields::statusAndSort(withHomepage: true, homepageField: 'show_on_homepage'),
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
