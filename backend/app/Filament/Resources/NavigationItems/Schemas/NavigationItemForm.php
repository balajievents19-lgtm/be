<?php

namespace App\Filament\Resources\NavigationItems\Schemas;

use App\Actions\Website\BuildNavigationPreviewUrlAction;
use App\Enums\NavigationLinkTarget;
use App\Filament\Support\WebsitePublishFields;
use App\Models\NavigationItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NavigationItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Menu Link')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Link Label')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('url')
                                            ->label('Link Address')
                                            ->required()
                                            ->maxLength(500)
                                            ->helperText('Example: /services or https://...'),
                                        Select::make('target')
                                            ->label('Open In')
                                            ->options(NavigationLinkTarget::options())
                                            ->default(NavigationLinkTarget::SameTab->value)
                                            ->required()
                                            ->native(false),
                                        TextInput::make('icon')
                                            ->maxLength(100)
                                            ->helperText('Optional icon class.'),
                                        Select::make('parent_id')
                                            ->label('Parent Menu Item')
                                            ->options(fn (): array => NavigationItem::query()->ordered()->pluck('label', 'id')->all())
                                            ->searchable()
                                            ->preload()
                                            ->native(false),
                                        TextInput::make('sort_order')
                                            ->label('Display Order')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->helperText('Lower numbers appear first.'),
                                        Placeholder::make('preview')
                                            ->label('Website Preview')
                                            ->content(fn (Get $get): string => app(BuildNavigationPreviewUrlAction::class)((string) $get('url')))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Menu Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('navigation')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']),
                                    ]),
                            ]),
                        Tab::make('SEO')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(255),
                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                            ]),
                        Tab::make('Publish')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('status')
                                            ->label('Published')
                                            ->helperText('Turn off to hide this link.')
                                            ->default(true)
                                            ->required()
                                            ->inline(false),
                                        Toggle::make('is_visible')
                                            ->label('Visible')
                                            ->default(true)
                                            ->required()
                                            ->inline(false),
                                        Toggle::make('show_on_header')
                                            ->label('Show in Header')
                                            ->default(true)
                                            ->inline(false),
                                        Toggle::make('show_on_footer')
                                            ->label('Show in Footer')
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
