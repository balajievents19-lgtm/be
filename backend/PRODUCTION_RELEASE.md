# Production Release — Git & SEO Pinning

Concise release continuity guide for Balaji Royal Events. Full server runbook: [DEPLOYMENT.md](./DEPLOYMENT.md).

## Repository layout (do not flatten)

| Repo | Path | Remote | Branch |
|------|------|--------|--------|
| Backend (Laravel/Filament API + Admin) | `backend/` | `https://github.com/balajievents19-lgtm/be-backend.git` | `main` |
| Frontend (Nuxt SSR) | `frontend/` | `https://github.com/balajievents19-lgtm/be.git` | `main` |
| Workspace shell | `C:\laragon\www\be` (root) | `https://github.com/balajievents19-lgtm/be.git` | **no commits** — not a release source |

**Release strategy:** pin and deploy from the **nested** `backend` and `frontend` repositories only. Do **not** convert to a monorepo, remove nested `.git` directories, or treat the empty root history as authoritative.

## Why a release SHA is not auto-pinned yet

As of this document, both nested repos track `origin/main` but contain a **large uncommitted working tree** (CMS IA, Leads CRM, Spatie RBAC, Users security, SEO, Gallery FE, Packages, production security, migrations, tests).

That tree is too broad to safely force into a single unreviewed “release” commit. Creating a fake commit only to make `git status` clean would misrepresent review state.

**Safe pinning process (manual, when ready):**

1. Review backend and frontend diffs separately (no secrets: `.env`, keys, tokens).
2. Commit intentional release candidate(s) on each nested `main` (split by concern if preferred).
3. Tag both repos with the **same** semver label, e.g. `v1.0.0-rc.1`.
4. Record both SHAs in the deploy ticket / this file’s release log.
5. Deploy those tags/SHAs only (never “whatever is on disk”).

Until steps 1–4 happen, production must not assume git cleanliness equals release readiness.

## Deployment order

1. **Backend:** pull pinned SHA → `composer install --no-dev` → `php artisan migrate --force` → `php artisan optimize` → queue restart.
2. Set/verify **`SITE_URL`** (public frontend origin) and **`ADMIN_EMAILS`** on the server `.env`.
3. Clear SEO/config caches (see below) after any `SITE_URL` change.
4. **Frontend:** pull matching pinned SHA → set `NUXT_PUBLIC_API_BASE` → `pnpm install --frozen-lockfile` → `pnpm build` → restart Nuxt process.

## Required environment (SEO host)

Never hardcode the live domain in source. Set on the server:

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Laravel/API origin (admin, `/sitemap.xml`, `/robots.txt` host) |
| `SITE_URL` | **Public website origin** for canonicals, Open Graph, JSON-LD, sitemap `<loc>` |
| `FRONTEND_URL` | Optional alias; used only if `SITE_URL` is empty (`config/seo.php`) |
| Admin **Canonical URL** (`settings.canonical_url`) | Used when `SITE_URL` / `FRONTEND_URL` are unset |

Precedence in `SeoService::siteUrl()`:

1. `config('seo.site_url')` ← `SITE_URL` or `FRONTEND_URL`
2. Admin SEO / settings `canonical_url`
3. `APP_URL` (last resort — wrong for public SEO if API host ≠ website)

Templates: `.env.example`, `.env.production.example`.

## After changing `SITE_URL`

Sitemap and global schema are cached (`seo.sitemap.xml`, `seo.schema.global`). Config may also be cached in production.

```bash
cd /var/www/balaji-events/backend
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

Then verify:

```bash
curl -sS "$APP_URL/api/seo/resolve?type=home" | head
curl -sS "$APP_URL/sitemap.xml" | head
```

Canonical / OG / sitemap `<loc>` must use the **public** `SITE_URL` host, not the API host.

## Rollback

```bash
# Backend
cd /var/www/balaji-events/backend
git fetch && git checkout <previous-backend-tag-or-sha>
composer install --no-dev --optimize-autoloader
php artisan optimize
php artisan queue:restart

# Frontend (matching previous release)
cd /var/www/balaji-events/frontend
git fetch && git checkout <previous-frontend-tag-or-sha>
pnpm install --frozen-lockfile
pnpm build
sudo supervisorctl restart balaji-events-nuxt
```

Prefer tagged pairs. Do not roll back only one side unless the mismatch is understood.

## Release log (fill when pinning)

| Tag | Backend SHA | Frontend SHA | Date | Notes |
|-----|-------------|--------------|------|-------|
| _(none yet)_ | `ae505bd` + dirty tree | `94ecb5d` + dirty tree | — | Working tree not release-pinned |
