# Google Reviews & Business Profile

This website shows **real Google reviews** when server credentials are configured.  
It never fabricates ratings, review text, or API responses.

## What is implemented

1. Admin fields: **Place ID** and **Google reviews URL** (`Website → Contact → Google Reviews`).
2. Laravel `GET /api/google-reviews` and the same payload nested in `GET /api/home`.
3. Cache: **6 hours** (`google.places.reviews.payload`). Homepage cache is separate (5 minutes).
4. Nuxt homepage section **Google Reviews** — hidden unless configured or a profile URL exists.
5. Admin **Refresh Google reviews** action.
6. Scheduled refresh: `php artisan google:reviews-refresh` twice daily (07:00 and 19:00) via Laravel scheduler.
7. Review **replies** stay disabled until Google Business Profile OAuth is fully configured and tested.

## Required credentials (server only)

Never put these in `NUXT_PUBLIC_*` or frontend code.

| Variable | Required for | Notes |
|----------|----------------|-------|
| `GOOGLE_PLACES_API_KEY` | Fetching reviews | Google Cloud **Places API (New)** key, restricted to the server IP if possible |
| `GOOGLE_PLACE_ID` or Admin Place ID | Identifying the listing | Maps Place ID for the Balaji Royal Events location |
| `GOOGLE_REVIEWS_URL` or Admin URL | “View all reviews on Google” | Public Maps / Business Profile URL |

Optional (review replies — **not live** until OAuth is completed):

| Variable | Purpose |
|----------|---------|
| `GOOGLE_GBP_CLIENT_ID` | Business Profile API OAuth client |
| `GOOGLE_GBP_CLIENT_SECRET` | OAuth secret |
| `GOOGLE_GBP_REDIRECT_URI` | OAuth callback (admin) |
| `GOOGLE_GBP_ACCOUNT_ID` | GBP account |
| `GOOGLE_GBP_LOCATION_ID` | GBP location |

## Enable live reviews

1. Create a Google Cloud project.
2. Enable **Places API (New)**.
3. Create an API key. Restrict it. Do not commit it.
4. Find the Place ID for the real business listing.
5. Set env vars on the Laravel host. Set Place ID in admin if not using env.
6. In Filament, open **Google Reviews** and click **Refresh Google reviews** (or run `php artisan google:reviews-refresh`).
7. Confirm the scheduler is running (`* * * * * php artisan schedule:run`).
7. Confirm the homepage shows reviewer names, ratings, and unmodified text.
8. Confirm admin status does **not** display the API key.

Until a successful fetch occurs, the public API returns `configured: false` or an empty `reviews` array with a safe error message.

## Privacy

Reviews are cached on the server. The Nuxt app never calls Google directly.
