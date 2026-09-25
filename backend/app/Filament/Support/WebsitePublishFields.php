<?php

namespace App\Filament\Support;

use App\Support\Staff\StaffContentAccess;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared Website CMS publish / visibility fields for business owners.
 */
class WebsitePublishFields
{
    /**
     * @return array<int, mixed>
     */
    public static function statusAndSort(bool $withHomepage = false, string $homepageField = 'homepage_featured'): array
    {
        $fields = [
            Toggle::make('status')
                ->label('Published')
                ->helperText('Turn off to hide this from the website. Staff cannot publish; Super Admin approves first.')
                ->default(true)
                ->inline(false)
                ->disabled(fn (): bool => ! StaffContentAccess::canPublish(Auth::user()))
                ->dehydrated(fn (): bool => StaffContentAccess::canPublish(Auth::user()))
                ->required(),
            TextInput::make('sort_order')
                ->label('Display Order')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText('Lower numbers appear first. You can also drag rows in the list.'),
        ];

        if ($withHomepage) {
            $fields[] = Toggle::make($homepageField)
                ->label('Show on Homepage')
                ->default(false)
                ->inline(false);
        }

        return $fields;
    }

    /**
     * @return array<int, mixed>
     */
    public static function schedule(): array
    {
        return [
            DateTimePicker::make('publish_at')
                ->label('Publish From')
                ->seconds(false)
                ->helperText('Optional. Leave empty to publish immediately when status is on.'),
            DateTimePicker::make('unpublish_at')
                ->label('Publish Until')
                ->seconds(false)
                ->helperText('Optional. Leave empty to keep it published until turned off.'),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function audit(): array
    {
        return [
            Placeholder::make('created_at_display')
                ->label('Created')
                ->content(fn (?Model $record): string => $record?->created_at?->toDayDateTimeString() ?? '—'),
            Placeholder::make('creator_display')
                ->label('Created by')
                ->content(fn (?Model $record): string => $record?->creator?->name ?? '—'),
            Placeholder::make('updated_at_display')
                ->label('Last Updated')
                ->content(fn (?Model $record): string => $record?->updated_at?->toDayDateTimeString() ?? '—'),
            Placeholder::make('editor_display')
                ->label('Last edited by')
                ->content(fn (?Model $record): string => $record?->editor?->name ?? '—'),
            Placeholder::make('reviewer_display')
                ->label('Reviewed by')
                ->content(fn (?Model $record): string => $record?->reviewer?->name ?? '—'),
            Placeholder::make('reviewed_at_display')
                ->label('Reviewed at')
                ->content(fn (?Model $record): string => $record?->reviewed_at?->toDayDateTimeString() ?? '—'),
            Placeholder::make('moderation_notes_display')
                ->label('Rejection reason')
                ->content(fn (?Model $record): string => filled($record?->moderation_notes) ? (string) $record->moderation_notes : '—')
                ->columnSpanFull(),
        ];
    }

    /**
     * Preview helper text for a frontend path.
     */
    public static function previewUrl(?string $path): string
    {
        $site = rtrim((string) (config('seo.site_url') ?: config('app.url')), '/');

        if (! $path) {
            return $site;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return $site.'/'.ltrim($path, '/');
    }

    public static function previewPlaceholder(string $path = '/', string $name = 'preview_url'): Placeholder
    {
        return Placeholder::make($name)
            ->label('Website Preview')
            ->content(fn (Get $get): string => self::previewUrl(
                filled($get('slug')) ? rtrim($path, '/').'/'.$get('slug') : $path
            ));
    }

    /**
     * Blog uses published_at as the start of the window.
     *
     * @return array<int, mixed>
     */
    public static function blogSchedule(): array
    {
        return [
            DateTimePicker::make('published_at')
                ->label('Publish From')
                ->seconds(false)
                ->default(now())
                ->helperText('When this post should appear on the website.'),
            DateTimePicker::make('unpublish_at')
                ->label('Publish Until')
                ->seconds(false)
                ->helperText('Optional. Leave empty to keep it published until turned off.'),
        ];
    }
}
