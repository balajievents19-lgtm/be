# Balaji Events — Backend (API & Admin)

Laravel **13** + Filament **4** CMS and public JSON API for the Balaji Events website.

Public site (Nuxt SSR) lives in the sibling **frontend** repository.

## Stack

- PHP 8.3+, Laravel 13, Filament 4
- MySQL 8+
- Queue / cache: database (Redis optional)
- Admin: `/admin` (Filament)

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Optional: copy `.env.production.example` when preparing a production host.

Set `ADMIN_EMAILS` for Filament access outside local (required when `APP_ENV=production`).

## Useful endpoints

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/home` | Homepage aggregator |
| GET | `/api/settings` | Site settings |
| GET | `/api/services`, `/api/services/{slug}` | Services |
| GET | `/api/gallery`, `/api/blog`, `/api/faqs` | Content lists (+ slug detail where applicable) |
| POST | `/api/contact` | Contact enquiry (throttle 5/min) |
| POST | `/api/newsletter` | Newsletter (throttle 5/min) |
| GET | `/up` | Health check |
| GET | `/sitemap.xml`, `/robots.txt` | SEO |

## Tests

```bash
php artisan test
vendor/bin/pint
```

## Production

See **[DEPLOYMENT.md](./DEPLOYMENT.md)** and **[PRODUCTION_CHECKLIST.md](./PRODUCTION_CHECKLIST.md)**.

Configs:

- `deploy/nginx/balaji-events.conf`
- `deploy/supervisor/balaji-events-worker.conf`
- `deploy/supervisor/balaji-events-nuxt.conf`
- `deploy/cron/laravel-scheduler`
- `.env.production.example`

## License

Proprietary — Balaji Events.
