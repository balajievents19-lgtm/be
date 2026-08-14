# Frontend deployment notes — Balaji Events 1.0

This document covers the **Nuxt SSR** app only. For Ubuntu packages, Nginx, SSL, MySQL, queue, cron, and full-stack procedure, use the backend **[DEPLOYMENT.md](../backend/DEPLOYMENT.md)**.

Release pinning (nested repos, tags, rollback): **[PRODUCTION_RELEASE.md](./PRODUCTION_RELEASE.md)** and backend **[PRODUCTION_RELEASE.md](../backend/PRODUCTION_RELEASE.md)**.

## Requirements

- Node.js **LTS** (20+ / 22+)
- **PNPM** 11.x (see `packageManager` in `package.json`)
- Reachable Laravel API (`NUXT_PUBLIC_API_BASE`)

## Environment

```bash
cp .env.production.example .env
```

| Variable | Example | Notes |
|----------|---------|-------|
| `NUXT_PUBLIC_API_BASE` | `https://api.example.com/api` | Baked at **build** time |
| `HOST` / `NITRO_HOST` | `127.0.0.1` | Bind local; Nginx proxies |
| `PORT` / `NITRO_PORT` | `3000` | Must match Nginx upstream |
| `NODE_ENV` | `production` | |

## Build & run

```bash
git pull
pnpm install --frozen-lockfile
pnpm build
# process managed by Supervisor — see backend deploy/supervisor/balaji-events-nuxt.conf
sudo supervisorctl restart balaji-events-nuxt
```

Manual preview:

```bash
node .output/server/index.mjs
```

## Nginx

Frontend is reverse-proxied; sample server block is in backend `deploy/nginx/balaji-events.conf` (`www.example.com` → `127.0.0.1:3000`).

## Validation before release

```bash
pnpm lint
pnpm typecheck
pnpm build
pnpm test:e2e   # optional smoke against preview
```
