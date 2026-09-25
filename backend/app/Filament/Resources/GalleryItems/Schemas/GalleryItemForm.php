<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Enums\ContentModerationStatus;
use App\Enums\GalleryMediaType;
use App\Enums\GalleryVideoSource;
use App\Filament\Support\WebsitePublishFields;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Support\Media\GalleryVideoEmbed;
use App\Support\Staff\StaffContentAccess;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
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
                                Section::make('Moderation')
                                    ->visible($staffMode)
                                    ->schema([
                                        Placeholder::make('staff_moderation_status')
                                            ->label('Status')
                                            ->content(function (?GalleryItem $record): string {
                                                if ($record === null) {
                                                    return 'New post — save as draft or submit for review.';
                                                }
                                                $status = ContentModerationStatus::tryFrom((string) $record->moderation_status);

                                                return $status?->label() ?? 'Draft';
                                            }),
                                        Placeholder::make('staff_rejection_reason')
                                            ->label('Rejection reason')
                                            ->visible(fn (?GalleryItem $record): bool => $record !== null
                                                && (string) $record->moderation_status === ContentModerationStatus::Rejected->value)
                                            ->content(fn (?GalleryItem $record): string => filled($record?->moderation_notes)
                                                ? (string) $record->moderation_notes
                                                : 'No reason provided.')
                                            ->columnSpanFull(),
                                    ]),
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
                                                        $query->whereIn('gallery_categories.id', $ids !== [] ? $ids : [0]);
                                                    }

                                                    return $query;
                                                },
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(function () use ($assignedCategoryIds): ?int {
                                                $ids = $assignedCategoryIds();

                                                return count($ids) === 1 ? $ids[0] : null;
                                            })
                                            ->visible(fn (): bool => ! $staffMode || count($assignedCategoryIds()) <= 1)
                                            ->disabled(fn (): bool => $staffMode && count($assignedCategoryIds()) === 1)
                                            ->dehydrated(fn (): bool => ! $staffMode || count($assignedCategoryIds()) <= 1),
                                        ToggleButtons::make('gallery_category_id')
                                            ->label('Gallery Category')
                                            ->options(function () use ($assignedCategoryIds): array {
                                                $ids = $assignedCategoryIds();
                                                if ($ids === []) {
                                                    return [];
                                                }

                                                return GalleryCategory::query()
                                                    ->whereIn('id', $ids)
                                                    ->ordered()
                                                    ->pluck('name', 'id')
                                                    ->all();
                                            })
                                            ->inline()
                                            ->required()
                                            ->default(function () use ($assignedCategoryIds): ?int {
                                                $ids = $assignedCategoryIds();

                                                return count($ids) === 1 ? $ids[0] : null;
                                            })
                                            ->visible(fn (): bool => $staffMode && count($assignedCategoryIds()) > 1)
                                            ->dehydrated(fn (): bool => $staffMode && count($assignedCategoryIds()) > 1)
                                            ->columnSpanFull(),
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
                                                        $query->whereIn('services.id', $ids !== [] ? $ids : [0]);
                                                    }

                                                    return $query;
                                                },
                                            )
                                            ->searchable()
                                            ->bulkToggleable()
                                            ->columns(fn (): int => $staffMode ? 3 : 2)
                                            ->columnSpanFull()
                                            ->extraAttributes(['class' => 'overflow-x-auto'])
                                            ->default(function () use ($assignedServiceIds): array {
                                                $ids = $assignedServiceIds();

                                                return count($ids) === 1 ? $ids : [];
                                            })
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
                            ->schema([
                                Section::make('SEO preview')
                                    ->columns(2)
                                    ->schema([
                                        Placeholder::make('seo_title_preview')
                                            ->label('Title preview')
                                            ->content(fn (Get $get): string => (string) ($get('seo_title') ?: $get('title') ?: '—')),
                                        Placeholder::make('seo_url_preview')
                                            ->label('URL / slug')
                                            ->content(fn (Get $get): string => WebsitePublishFields::previewUrl(
                                                filled($get('slug')) ? '/gallery/'.$get('slug') : '/gallery'
                                            )),
                                        Placeholder::make('seo_description_preview')
                                            ->label('Description preview')
                                            ->content(fn (Get $get): string => Str::limit((string) ($get('seo_description') ?: $get('description') ?: $get('caption') ?: '—'), 160))
                                            ->columnSpanFull(),
                                        Placeholder::make('seo_social_preview')
                                            ->label('Social preview')
                                            ->content(fn (Get $get): string => trim(
                                                (string) ($get('seo_title') ?: $get('title') ?: 'Title')."\n".
                                                Str::limit((string) ($get('seo_description') ?: $get('description') ?: ''), 120)
                                            ))
                                            ->columnSpanFull(),
                                    ]),
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
                                        TextInput::make('seo_keywords')
                                            ->label('Primary / related keywords')
                                            ->maxLength(255)
                                            ->helperText('Short, natural keywords. Do not stuff.')
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
