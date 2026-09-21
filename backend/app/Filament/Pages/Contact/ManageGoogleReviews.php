<?php

namespace App\Filament\Pages\Contact;

use App\Filament\Clusters\ContactCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Services\Google\GoogleReviewsService;
use App\Support\ContentCache;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class ManageGoogleReviews extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'contact';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ContactCluster::class;

    protected static ?string $navigationLabel = 'Google Reviews';

    protected static ?string $title = 'Google Reviews';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $slug = 'google-reviews';

    protected function previewPath(): string
    {
        return '/';
    }

    protected function formFields(): array
    {
        $status = app(GoogleReviewsService::class)->status();

        $summary = sprintf(
            '<ul class="list-disc space-y-1 pl-4 text-sm">
                <li>Places API key: <strong>%s</strong> (env <code>GOOGLE_PLACES_API_KEY</code> — never stored in the database)</li>
                <li>Place ID: <strong>%s</strong></li>
                <li>Last fetch: <strong>%s</strong> (%s)</li>
                <li>Cache present: <strong>%s</strong></li>
                <li>Google Business Profile OAuth (review replies): <strong>%s</strong></li>
            </ul>
            <p class="mt-3 text-sm">The website shows live Google reviews only after a successful Places fetch. Empty credentials never produce fake reviews.</p>
            <p class="mt-2 text-sm">Required env: <code>GOOGLE_PLACES_API_KEY</code>. Optional: <code>GOOGLE_PLACE_ID</code>, <code>GOOGLE_REVIEWS_URL</code>, <code>GOOGLE_GBP_CLIENT_ID</code>, <code>GOOGLE_GBP_CLIENT_SECRET</code>, <code>GOOGLE_GBP_LOCATION_ID</code>.</p>',
            $status['places_api_key_configured'] ? 'configured' : 'missing',
            $status['place_id'] ?: 'missing',
            $status['last_fetched_at'] ?: 'never',
            $status['last_status'] ?? 'never',
            ! empty($status['cached']) ? 'yes' : 'no',
            ! empty($status['gbp_replies_ready']) ? 'ready (not enabled until tested)' : 'not configured — replies disabled',
        );

        if (! empty($status['last_error'])) {
            $summary .= '<p class="mt-2 text-sm text-danger-600">Last error: '.e((string) $status['last_error']).'</p>';
        }

        return [
            Placeholder::make('google_reviews_status')
                ->label('Integration status')
                ->content(new HtmlString($summary))
                ->columnSpanFull(),
            TextInput::make('google_place_id')
                ->label('Google Place ID')
                ->maxLength(255)
                ->helperText('From Google Maps / Place Details. Public identifier, not a secret.')
                ->columnSpanFull(),
            TextInput::make('google_reviews_url')
                ->label('Google reviews / Business Profile URL')
                ->url()
                ->maxLength(500)
                ->helperText('Used for “View all reviews on Google”.')
                ->columnSpanFull(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshGoogleReviews')
                ->label('Refresh Google reviews')
                ->icon(Heroicon::OutlinedArrowPath)
                ->requiresConfirmation()
                ->action(function (): void {
                    $service = app(GoogleReviewsService::class);
                    $payload = $service->refresh();
                    ContentCache::flush(ContentCache::HOME);
                    if (! empty($payload['configured']) && empty($payload['error'])) {
                        Notification::make()
                            ->title('Google reviews refreshed')
                            ->body(sprintf(
                                '%s reviews cached. Rating: %s',
                                count($payload['reviews'] ?? []),
                                $payload['rating'] !== null ? (string) $payload['rating'] : 'n/a',
                            ))
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Google reviews not live')
                        ->body($payload['error'] ?: 'Add GOOGLE_PLACES_API_KEY and a Place ID, then try again.')
                        ->warning()
                        ->send();
                }),
            ...parent::getHeaderActions(),
        ];
    }
}
