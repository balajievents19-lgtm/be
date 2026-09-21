# Balaji Royal Events — Frontend (Nuxt SSR)

Nuxt **4** + Vue 3 public website for Balaji Royal Events. Consumes the Laravel API in the sibling **backend** repository.

## Stack

- Nuxt 4 / Vue 3 / TypeScript / Tailwind (Nuxt UI)
- PNPM package manager
- SSR (+ homepage prerender)

## Local setup

```bash
cp .env.example .env   # or .env.production.example for prod-like values
pnpm install
pnpm dev
```

Set API base (defaults to `http://127.0.0.1:8000/api` in `nuxt.config.ts`):

```bash
# .env
NUXT_PUBLIC_API_BASE=http://127.0.0.1:8000/api
```

## Scripts

| Command | Purpose |
|---------|---------|
| `pnpm dev` | Development server |
| `pnpm build` | Production Nitro build |
| `pnpm preview` | Preview `.output` |
| `pnpm lint` | ESLint |
| `pnpm typecheck` | `nuxt typecheck` |
| `pnpm test:e2e` | Playwright smoke (requires build) |

## Production

Full stack runbook (Nginx, Supervisor, SSL, backups): see the backend repo **[DEPLOYMENT.md](../backend/DEPLOYMENT.md)** (or `backend/DEPLOYMENT.md` on the server).

Frontend-specific:

1. Copy [`.env.production.example`](./.env.production.example) → `.env`
2. Set `NUXT_PUBLIC_API_BASE=https://www.balajiroyalevents.com/api`
3. `pnpm install --frozen-lockfile && pnpm build`
4. Run `node .output/server/index.mjs` (Supervisor sample lives in backend `deploy/supervisor/balaji-events-nuxt.conf`)

Also see [PRODUCTION_CHECKLIST.md](./PRODUCTION_CHECKLIST.md) and [CHANGELOG.md](./CHANGELOG.md).

## License

Proprietary — Balaji Royal Events.
