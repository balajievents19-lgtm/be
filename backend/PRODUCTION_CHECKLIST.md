# Production checklist — Balaji Royal Events 1.0

Use before go-live and after each production deploy.

## Infrastructure

- [ ] Ubuntu 24.04 LTS provisioned
- [ ] PHP 8.3-FPM + required extensions
- [ ] MySQL 8 database + app user (least privilege)
- [ ] Nginx site enabled; `nginx -t` clean
- [ ] SSL certificates installed (Certbot / Cloudflare Full strict)
- [ ] Supervisor: queue worker + Nuxt process running
- [ ] Cron: `schedule:run` every minute
- [ ] Firewall: 80/443 open; SSH restricted; MySQL not public

## Backend env

- [ ] `.env` from `.env.production.example`
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` set
- [ ] `APP_URL` = `https://www.balajiroyalevents.com`
- [ ] `SITE_URL` / `FRONTEND_URL` = `https://www.balajiroyalevents.com`
- [ ] `ADMIN_EMAILS` includes all Filament users
- [ ] DB credentials verified
- [ ] Mail SMTP tested (`php artisan tinker` / real contact)
- [ ] `FILESYSTEM_DISK=public` + `storage:link`
- [ ] Queue + cache drivers chosen (`database` or `redis`)

## Frontend env / build

- [ ] `NUXT_PUBLIC_API_BASE=https://www.balajiroyalevents.com/api`
- [ ] `pnpm build` succeeds on the server
- [ ] Nuxt process listens on `127.0.0.1:3000` only
- [ ] Nginx proxies public host to Nuxt

## Release commands

- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link`
- [ ] `php artisan optimize`
- [ ] `php artisan queue:restart`
- [ ] `pnpm install --frozen-lockfile && pnpm build`
- [ ] Supervisor restarted for Nuxt after build

## Functional smoke

- [ ] `GET /up` → 200
- [ ] `GET /api/home` → 200 JSON
- [ ] Homepage, About, Services, Gallery, Blog, FAQ, Contact render
- [ ] Contact form → CRM enquiry
- [ ] Newsletter form → subscriber row
- [ ] Filament CMS edit reflects on site (within cache TTL / after save)
- [ ] `sitemap.xml` + `robots.txt` reachable
- [ ] Mobile viewport check on homepage + contact

## Ops

- [ ] Log rotation confirmed
- [ ] DB backup scheduled + restore tested
- [ ] Upload disk backup (or S3) scheduled
- [ ] Monitoring / uptime ping on `/up` and homepage
- [ ] Search Console + Analytics IDs set in admin SEO settings

## Sign-off

| Role | Name | Date |
|------|------|------|
| Engineering | | |
| Content / Admin | | |
