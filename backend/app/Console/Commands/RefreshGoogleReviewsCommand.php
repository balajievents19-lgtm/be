<?php

namespace App\Console\Commands;

use App\Services\Google\GoogleReviewsService;
use Illuminate\Console\Command;

class RefreshGoogleReviewsCommand extends Command
{
    protected $signature = 'google:reviews-refresh';

    protected $description = 'Fetch and cache Google Business / Places reviews for the public website';

    public function handle(GoogleReviewsService $reviews): int
    {
        $payload = $reviews->refresh();

        if (! ($payload['configured'] ?? false)) {
            $this->warn('Google reviews are not configured. Set GOOGLE_PLACES_API_KEY and a Place ID.');

            return self::SUCCESS;
        }

        if (! empty($payload['error']) && empty($payload['reviews'])) {
            $this->error((string) $payload['error']);

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Cached %d Google review(s). Rating: %s',
            count($payload['reviews'] ?? []),
            $payload['rating'] !== null ? (string) $payload['rating'] : 'n/a',
        ));

        return self::SUCCESS;
    }
}
