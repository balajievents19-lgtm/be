# Production Release — Frontend

Nuxt SSR app for Balaji Events.

- **Repository:** this nested git repo (`frontend/`)
- **Remote:** `https://github.com/balajievents19-lgtm/be.git`
- **Release branch:** `main`
- **Full pinning / SEO / rollback:** see sibling [`../backend/PRODUCTION_RELEASE.md`](../backend/PRODUCTION_RELEASE.md)
- **Server runbook:** [`../backend/DEPLOYMENT.md`](../backend/DEPLOYMENT.md) and [DEPLOYMENT.md](./DEPLOYMENT.md)

## Pinning

Deploy only from a reviewed commit or tag that matches the backend release pair. Do not treat the empty workspace-root git history as a release source. Do not remove this nested `.git`.

Large uncommitted working-tree changes may still be present; do not assume `main` on disk equals a tagged release until an intentional commit/tag exists.

## Build env

Set `NUXT_PUBLIC_API_BASE` to the Laravel API `/api` base **before** `pnpm build`.
