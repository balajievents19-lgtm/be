<?php

namespace App\Services\Google;

use App\Models\Setting;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleReviewsService
{
    public const CACHE_KEY = 'google.places.reviews.payload';

    public const STATUS_KEY = 'google.places.reviews.status';

    public const TTL_SECONDS = 21600;

    /**
     * Public website payload. Never includes API keys or raw Google error bodies.
     *
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        $status = $this->status();

        if (! $status['places_configured']) {
            return $this->emptyPublicPayload(
                configured: false,
                error: null,
            );
        }

        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        return $this->refresh();
    }

    /**
     * Fetch from Google Places API (New) and cache. Fail closed — never invent reviews.
     *
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        $status = $this->status();

        if (! $status['places_configured']) {
            $payload = $this->emptyPublicPayload(false, null);
            $this->storeStatus($status, 'not_configured', null);

            return $payload;
        }

        try {
            $payload = $this->fetchFromPlaces($status['place_id']);
            Cache::put(self::CACHE_KEY, $payload, self::TTL_SECONDS);
            $this->storeStatus($status, 'ok', null);

            return $payload;
        } catch (Throwable $e) {
            report($e);
            Log::warning('Google Places reviews fetch failed', [
                'place_id' => $status['place_id'],
                'exception' => $e::class,
            ]);

            $this->storeStatus($status, 'error', 'Google reviews are temporarily unavailable.');

            $stale = Cache::get(self::CACHE_KEY);
            if (is_array($stale) && ($stale['reviews'] ?? []) !== []) {
                $stale['error'] = 'Showing last cached Google reviews.';
                $stale['stale'] = true;

                return $stale;
            }

            return $this->emptyPublicPayload(true, 'Google reviews are temporarily unavailable.');
        }
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::STATUS_KEY);
    }

    /**
     * Admin-only status. Secrets are booleans only.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $settings = Setting::query()->first();
        $apiKey = trim((string) config('services.google_places.api_key'));
        $placeId = trim((string) (
            $settings?->google_place_id
            ?: config('services.google_places.place_id')
            ?: ''
        ));
        $profileUrl = trim((string) (
            $settings?->google_reviews_url
            ?: config('services.google_places.reviews_url')
            ?: ''
        ));

        $gbpClient = trim((string) config('services.google_business_profile.client_id'));
        $gbpSecret = trim((string) config('services.google_business_profile.client_secret'));
        $gbpLocation = trim((string) config('services.google_business_profile.location_id'));

        $stored = Cache::get(self::STATUS_KEY, []);

        return [
            'places_api_key_configured' => $apiKey !== '',
            'place_id' => $placeId !== '' ? $placeId : null,
            'place_id_configured' => $placeId !== '',
            'places_configured' => $apiKey !== '' && $placeId !== '',
            'profile_url' => $profileUrl !== '' ? $profileUrl : null,
            'gbp_oauth_configured' => $gbpClient !== '' && $gbpSecret !== '',
            'gbp_location_configured' => $gbpLocation !== '',
            'gbp_replies_ready' => $gbpClient !== '' && $gbpSecret !== '' && $gbpLocation !== '',
            'last_status' => $stored['last_status'] ?? 'never',
            'last_error' => $stored['last_error'] ?? null,
            'last_fetched_at' => $stored['last_fetched_at'] ?? null,
            'cached' => Cache::has(self::CACHE_KEY),
        ];
    }

    /**
     * Review replies require Google Business Profile OAuth + location access.
     * Disabled until credentials exist — never posts without authorization.
     */
    public function canReplyFromAdmin(): bool
    {
        return (bool) $this->status()['gbp_replies_ready'];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFromPlaces(string $placeId): array
    {
        $resource = str_starts_with($placeId, 'places/') ? $placeId : 'places/'.$placeId;
        $apiKey = (string) config('services.google_places.api_key');

        $response = Http::timeout(12)
            ->acceptJson()
            ->withHeaders([
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => 'id,displayName,rating,userRatingCount,googleMapsUri,reviews',
            ])
            ->get('https://places.googleapis.com/v1/'.$resource);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new \RuntimeException('Unexpected Google Places response.');
        }

        $reviews = [];
        foreach ($data['reviews'] ?? [] as $review) {
            if (! is_array($review)) {
                continue;
            }

            $text = $review['text']['text'] ?? $review['originalText']['text'] ?? '';
            $author = $review['authorAttribution']['displayName'] ?? null;

            $reviews[] = [
                'author_name' => is_string($author) && $author !== '' ? $author : 'Google user',
                'rating' => isset($review['rating']) ? (int) $review['rating'] : null,
                'text' => is_string($text) ? $text : '',
                'publish_time' => $review['publishTime'] ?? null,
                'relative_time' => $review['relativePublishTimeDescription'] ?? null,
                'author_url' => $review['authorAttribution']['uri'] ?? null,
                'profile_photo_url' => $review['authorAttribution']['photoUri'] ?? null,
            ];
        }

        $mapsUrl = $data['googleMapsUri']
            ?? Setting::query()->value('google_reviews_url')
            ?? config('services.google_places.reviews_url');

        return [
            'configured' => true,
            'enabled' => true,
            'source' => 'google_places',
            'rating' => isset($data['rating']) ? (float) $data['rating'] : null,
            'review_count' => isset($data['userRatingCount']) ? (int) $data['userRatingCount'] : count($reviews),
            'maps_url' => is_string($mapsUrl) && $mapsUrl !== '' ? $mapsUrl : null,
            'fetched_at' => now()->toIso8601String(),
            'stale' => false,
            'error' => null,
            'reviews' => $reviews,
        ];
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function storeStatus(array $status, string $lastStatus, ?string $error): void
    {
        Cache::put(self::STATUS_KEY, [
            'last_status' => $lastStatus,
            'last_error' => $error,
            'last_fetched_at' => now()->toIso8601String(),
            'places_configured' => $status['places_configured'],
        ], self::TTL_SECONDS * 4);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPublicPayload(bool $configured, ?string $error): array
    {
        $mapsUrl = Setting::query()->value('google_reviews_url')
            ?: config('services.google_places.reviews_url');

        return [
            'configured' => $configured,
            'enabled' => false,
            'source' => 'google_places',
            'rating' => null,
            'review_count' => null,
            'maps_url' => is_string($mapsUrl) && $mapsUrl !== '' ? $mapsUrl : null,
            'fetched_at' => null,
            'stale' => false,
            'error' => $error,
            'reviews' => [],
        ];
    }
}
