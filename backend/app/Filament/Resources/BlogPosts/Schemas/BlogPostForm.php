<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Filament\Support\WebsitePublishFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Blog Post')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('blog_category_id')
                                            ->label('Category')
                                            ->relationship(
                                                name: 'category',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->ordered(),
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required(),
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
                                        WebsitePublishFields::previewPlaceholder('/blog'),
                                        TextInput::make('author')
                                            ->maxLength(255)
                                            ->default('Balaji Events'),
                                        Textarea::make('excerpt')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->helperText('Short summary used in listings and meta fallbacks.'),
                                        RichEditor::make('content')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'h2',
                                                'h3',
                                                'blockquote',
                                                'undo',
                                                'redo',
                                            ]),
                                        TagsInput::make('tags')
                                            ->placeholder('Add tag')
                                            ->columnSpanFull(),
                                        TextInput::make('reading_time')
                                            ->label('Reading Time (minutes)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Leave blank to auto-calculate from content.'),
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
                                            ->directory('blog/featured')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->maxSize(5120),
                                        FileUpload::make('banner_image')
                                            ->label('Banner Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('blog/banners')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->maxSize(5120),
                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail')
                                            ->image()
                                            ->disk('public')
                                            ->directory('blog/thumbnails')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->maxSize(2048),
                                        TextInput::make('alt_text')
                                            ->label('Alt Text')
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
                                        Textarea::make('seo_keywords')
                                            ->label('SEO Keywords')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        TextInput::make('canonical_url')
                                            ->label('Canonical URL')
                                            ->url()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Select::make('schema_type')
                                            ->label('Schema Type')
                                            ->options([
                                                'BlogPosting' => 'BlogPosting',
                                                'Article' => 'Article',
                                                'NewsArticle' => 'NewsArticle',
                                                'WebPage' => 'WebPage',
                                            ])
                                            ->default('BlogPosting')
                                            ->required(),
                                        FileUpload::make('opengraph_image')
                                            ->label('OpenGraph Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('blog/seo')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->maxSize(2048),
                                    ]),
                            ]),

                        Tab::make('Publish')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('status')
                                            ->label('Published')
                                            ->helperText('Turn off to hide this from the website.')
                                            ->default(true)
                                            ->inline(false),
                                        Toggle::make('featured')
                                            ->label('Featured')
                                            ->default(false)
                                            ->inline(false),
                                        Toggle::make('homepage_featured')
                                            ->label('Show on Homepage')
                                            ->default(false)
                                            ->inline(false),
                                        ...WebsitePublishFields::blogSchedule(),
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
