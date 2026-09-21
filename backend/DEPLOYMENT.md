# Balaji Royal Events — Production Deployment Guide

Version **1.0** production runbook for the Laravel API/admin and Nuxt SSR website.

Related docs:

- [PRODUCTION_RELEASE.md](./PRODUCTION_RELEASE.md) — git pinning, dual-repo strategy, SEO host
- [PRODUCTION_CHECKLIST.md](./PRODUCTION_CHECKLIST.md)
- [CHANGELOG.md](./CHANGELOG.md)
- Frontend env: `../frontend/.env.production.example` (sibling repo)

---

## 1. Server requirements

| Component | Version / notes |
|-----------|-----------------|
| OS | **Ubuntu 24.04 LTS** |
| PHP | **8.3+** with extensions: `cli`, `fpm`, `mysql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `gd`, `intl`, `tokenizer`, `ctype`, `fileinfo`, `redis` (optional) |
| MySQL | **8+** |
| Nginx | Latest from Ubuntu/main |
| Node.js | **LTS** (20.x or 22.x) |
| Composer | 2.x |
| PNPM | 9+ / 10+ (frontend uses `packageManager: pnpm@11`) |
| Supervisor | For queue + Nuxt process |
| Certbot | Let's Encrypt SSL |

Suggested layout:

```text
/var/www/balaji-events/
  backend/     # this Laravel repo
  frontend/    # Nuxt repo
```

Suggested hostnames (same public origin):

- `https://www.balajiroyalevents.com` → Nuxt SSR (`SITE_URL` / `FRONTEND_URL`)
- `https://www.balajiroyalevents.com/api` → Laravel API (`APP_URL` + `/api`)
- `https://www.balajiroyalevents.com/admin` → Filament

---

## 2. Initial server packages

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server redis-server supervisor git unzip curl \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-redis

# Node LTS (example via NodeSource or nvm)
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pnpm@11

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Create DB user and database in MySQL 8, then continue.

---

## 3. Environment

### Backend

```bash
cd /var/www/balaji-events/backend
cp .env.production.example .env
php artisan key:generate
```

Set at minimum:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://www.balajiroyalevents.com`
- `DB_*` credentials
- `SITE_URL` / `FRONTEND_URL` = `https://www.balajiroyalevents.com` (canonical/OG/JSON-LD/sitemap)
- `FRONTEND_URL` = optional alias if `SITE_URL` unset
- `ADMIN_EMAILS` = comma-separated Filament login emails (**required in production**)
- `MAIL_*` for outbound mail
- `FILESYSTEM_DISK=public` for CMS uploads
- `QUEUE_CONNECTION=database` (or `redis`)
- `CACHE_STORE=database` (or `redis`)
- `SESSION_DRIVER=database`, tighten `SESSION_DOMAIN` / `SESSION_ENCRYPT`

### Frontend

```bash
cd /var/www/balaji-events/frontend
cp .env.production.example .env
```

Set `NUXT_PUBLIC_API_BASE=https://www.balajiroyalevents.com/api` **before** `pnpm build` (public runtime config is baked at build time).

---

## 4. Deploy / update procedure

Run from the release user with permission on `/var/www/balaji-events`.

```bash
# --- Backend ---
cd /var/www/balaji-events/backend
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:restart

# --- Frontend ---
cd /var/www/balaji-events/frontend
git pull
pnpm install --frozen-lockfile
pnpm build
sudo supervisorctl restart balaji-events-nuxt
```

First-time only: create `.env`, generate key, run migrations, link storage, install Nginx/Supervisor/cron (sections below).

### Permissions

```bash
sudo chown -R www-data:www-data /var/www/balaji-events/backend/storage /var/www/balaji-events/backend/bootstrap/cache
sudo find /var/www/balaji-events/backend/storage /var/www/balaji-events/backend/bootstrap/cache -type d -exec chmod 775 {} \;
sudo find /var/www/balaji-events/backend/storage /var/www/balaji-events/backend/bootstrap/cache -type f -exec chmod 664 {} \;
```

Deploy user should be in the `www-data` group (or use ACL) so `git pull` and `composer` still work.

---

## 5. Nginx

Sample config: [`deploy/nginx/balaji-events.conf`](./deploy/nginx/balaji-events.conf)

```bash
sudo cp deploy/nginx/balaji-events.conf /etc/nginx/sites-available/balaji-events
sudo ln -sf /etc/nginx/sites-available/balaji-events /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# SSL (after DNS points here)
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d www.balajiroyalevents.com -d balajiroyalevents.com
```

Cloudflare tip: set SSL mode to **Full (strict)** once origin certificates exist; cache HTML carefully for SSR pages.

---

## 6. Supervisor (queue + Nuxt)

```bash
sudo cp deploy/supervisor/balaji-events-worker.conf /etc/supervisor/conf.d/
sudo cp deploy/supervisor/balaji-events-nuxt.conf /etc/supervisor/conf.d/
sudo mkdir -p /var/log/balaji-events
sudo chown www-data:www-data /var/log/balaji-events
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start balaji-events-worker:*
sudo supervisorctl start balaji-events-nuxt
```

Queue restart after each backend deploy:

```bash
php artisan queue:restart
# or
sudo supervisorctl restart balaji-events-worker:*
```

---

## 7. Cron (scheduler)

```bash
sudo cp deploy/cron/laravel-scheduler /etc/cron.d/balaji-events-scheduler
sudo chmod 644 /etc/cron.d/balaji-events-scheduler
```

Laravel health endpoint (built-in): `GET https://www.balajiroyalevents.com/up`

---

## 8. Monitoring, logs, backups

### Logs

| Service | Location |
|---------|----------|
| Laravel app | `storage/logs/laravel.log` |
| Queue worker | `storage/logs/worker.log` |
| Nginx | `/var/log/nginx/access.log`, `error.log` |
| PHP-FPM | `/var/log/php8.3-fpm.log` |
| Nuxt SSR | `/var/log/balaji-events/nuxt.log` |

### Health checks

```bash
curl -fsS https://www.balajiroyalevents.com/up
curl -fsS -o /dev/null -w "%{http_code}\n" https://www.balajiroyalevents.com/
curl -fsS https://www.balajiroyalevents.com/api/home | head -c 200
curl -fsS https://www.balajiroyalevents.com/robots.txt
curl -fsS https://www.balajiroyalevents.com/sitemap.xml | head
```

### Cache clear (when content looks stale)

```bash
cd /var/www/balaji-events/backend
php artisan cache:clear
php artisan config:clear   # only while debugging; re-run optimize after
php artisan route:clear
php artisan view:clear
php artisan optimize       # restore production caches
```

Content API caches TTL is ~5 minutes (`ContentCache::TTL`); CMS saves flush relevant keys via observers.

### SEO host / SITE_URL change (required)

After setting or changing `SITE_URL` (or Admin canonical URL), flush config + SEO caches so sitemap/schema/meta do not keep the old host:

```bash
cd /var/www/balaji-events/backend
php artisan config:clear
php artisan cache:clear    # drops seo.sitemap.xml and seo.schema.global keys
php artisan optimize
```

Verify `GET /api/seo/resolve?type=home` and `GET /sitemap.xml` use the public website origin, not `APP_URL`.

### Queue restart

```bash
php artisan queue:restart
sudo supervisorctl status balaji-events-worker:*
```

### Backups (recommended)

1. **MySQL** daily dump (retain ≥ 7 days):

   ```bash
   mysqldump -u backup_user -p balaji_events | gzip > /var/backups/balaji/db-$(date +%F).sql.gz
   ```

2. **Uploads**: sync `storage/app/public` (or S3 bucket if used).

3. **Code**: rely on git tags/releases; keep last known-good commit SHA.

4. Test restore on a staging host quarterly.

### Search Console / Analytics

Configure in Filament → System → SEO / Website Settings:

- Google Analytics ID
- Google Search Console verification
- Facebook Pixel (optional)
- Canonical URL / robots

Submit `https://www.balajiroyalevents.com/sitemap.xml` in Search Console.

---

## 9. Security notes

- Never set `APP_DEBUG=true` in production.
- Always set `ADMIN_EMAILS` in production (empty allowlist blocks panel access when `APP_ENV=production`).
- Keep Filament on the API host behind strong passwords / optional IP allowlist or Cloudflare Access.
- Public contact/newsletter endpoints are throttled (`5/min`); API default `60/min`.
- CMS HTML is trusted admin content on FAQ/blog detail (`v-html`).

---

## 10. Rollback

```bash
cd /var/www/balaji-events/backend
git fetch && git checkout <previous-sha>
composer install --no-dev --optimize-autoloader
php artisan migrate --force   # only if migrations are reversible / planned
php artisan optimize
php artisan queue:restart

cd /var/www/balaji-events/frontend
git checkout <previous-sha>
pnpm install --frozen-lockfile
pnpm build
sudo supervisorctl restart balaji-events-nuxt
```

Prefer forward-fix hotfixes for failed migrations.

---

## 11. Post-deploy smoke

- [ ] Admin login (`/admin`) with an `ADMIN_EMAILS` user
- [ ] Homepage SSR loads CMS sections
- [ ] Contact form creates CRM enquiry
- [ ] Newsletter subscribe works
- [ ] `/api/home`, `/sitemap.xml`, `/robots.txt` OK
- [ ] HTTPS certificates renew (`certbot renew --dry-run`)
