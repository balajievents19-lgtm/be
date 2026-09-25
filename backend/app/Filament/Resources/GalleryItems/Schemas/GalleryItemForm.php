<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Enums\GalleryMediaType;
use App\Enums\GalleryVideoSource;
use App\Filament\Support\WebsitePublishFields;
use App\Support\Media\GalleryVideoEmbed;
use App\Support\Staff\StaffContentAccess;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GalleryItemForm
{
    public static function configure(Schema $schema, bool $staffMode = false): Schema
    {
        $isImage = fn (Get $get): bool => ($get('media_type') ?? GalleryMediaType::Image->value) !== GalleryMediaType::Video->value;
        $isVideo = fn (Get $get): bool => ($get('media_type') ?? GalleryMediaType::Image->value) === GalleryMediaType::Video->value;
        $assignedCategoryIds = function (): array {
            $user = Auth::user();
            if ($user === null) {
                return [];
            }

            return StaffContentAccess::accessibleGalleryCategoryIds($user);
        };
        $assignedServiceIds = function (): array {
            $user = Auth::user();
            if ($user === null) {
                return [];
            }

            return StaffContentAccess::accessibleServiceIds($user);
        };

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
                                        Radio::make('media_type')
                                            ->label('Media Type')
                                            ->options(GalleryMediaType::options())
                                            ->default(GalleryMediaType::Image->value)
                                            ->required()
                                            ->inline()
                                            ->live()
                                            ->columnSpanFull(),
                                        Select::make('gallery_category_id')
                                            ->label('Gallery Category')
                                            ->relationship(
                                                name: 'category',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function ($query) {
                                                    $user = Auth::user();
                                                    $query->ordered();
                                                    if ($user && ! StaffContentAccess::isSuperAdmin($user)) {
                                                        $ids = StaffContentAccess::accessibleGalleryCategoryIds($user);
                                                        $query->whereIn('id', $ids !== [] ? $ids : [0]);
                                                    }

                                                    return $query;
                                                },
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(fn (): ?int => $assignedCategoryIds()[0] ?? null)
                                            ->disabled(fn (): bool => $staffMode && count($assignedCategoryIds()) <= 1)
                                            ->dehydrated(),
                                        CheckboxList::make('services')
                                            ->label(fn (Get $get): string => $isVideo($get)
                                                ? 'Show this video in Services'
                                                : 'Show this photo in Services')
                                            ->relationship(
                                                name: 'services',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function ($query) {
                                                    $query->active()->ordered();
                                                    $user = Auth::user();
                                                    if ($user && ! StaffContentAccess::isSuperAdmin($user)) {
                                                        $ids = StaffContentAccess::accessibleServiceIds($user);
                                                        if ($ids !== []) {
                                                            $query->whereIn('id', $ids);
                                                        }
                                                    }

                                                    return $query;
                                                },
                                            )
                                            ->searchable()
                                            ->bulkToggleable()
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->default(fn (): array => $assignedServiceIds())
                                            ->helperText(fn (Get $get): string => $isVideo($get)
                                                ? 'The video is added once. It will appear in this Gallery Category and on every selected Service page.'
                                                : 'The photo is uploaded once. It will appear in this Gallery Category and on every selected Service page.'),
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
                                    ->visible($isImage)
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Image (public preview; original is secured privately)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('gallery/images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->required(fn (Get $get): bool => $isImage($get))
                                            ->maxSize(5120)
                                            ->helperText('On save, the original is moved to private storage. Public gallery shows a safe preview.')
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
                                            ->helperText('Prefer Media Type → Video for gallery videos. Do not upload video files.'),
                                        TextInput::make('vimeo_url')
                                            ->label('Vimeo URL (external — no video upload)')
                                            ->url()
                                            ->maxLength(255),
                                    ]),
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail')
                                            ->image()
                                            ->disk('public')
                                            ->directory('gallery/thumbnails')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->helperText(fn (Get $get): string => $isVideo($get)
                                                ? 'Optional if YouTube can provide a preview. Uses the same protected image storage as gallery photos.'
                                                : 'Optional public thumbnail for the gallery card.'),
                                    ]),
                                Section::make()
                                    ->columns(2)
                                    ->visible($isVideo)
                                    ->schema([
                                        Radio::make('video_source')
                                            ->label('Source')
                                            ->options(GalleryVideoSource::options())
                                            ->required(fn (Get $get): bool => $isVideo($get))
                                            ->inline()
                                            ->live()
                                            ->columnSpanFull(),
                                        TextInput::make('video_url')
                                            ->label(fn (Get $get): string => GalleryVideoSource::tryFrom((string) $get('video_source'))?->urlLabel() ?? 'Video URL')
                                            ->required(fn (Get $get): bool => $isVideo($get))
                                            ->url()
                                            ->maxLength(2048)
                                            ->columnSpanFull()
                                            ->helperText(fn (Get $get): string => GalleryVideoSource::tryFrom((string) $get('video_source'))?->urlHelper() ?? 'HTTPS URL. The video file is not stored on this server.')
                                            ->rules([
                                                fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                                    if (($get('media_type') ?? '') !== GalleryMediaType::Video->value) {
                                                        return;
                                                    }
                                                    $source = (string) $get('video_source');
                                                    $url = is_string($value) ? $value : '';
                                                    if (! GalleryVideoEmbed::sourceMatches($source, $url)) {
                                                        $fail('Enter a valid HTTPS URL for the selected source. External videos are not downloaded.');
                                                    }
                                                },
                                            ]),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->hidden($staffMode)
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
                            ->hidden($staffMode)
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
