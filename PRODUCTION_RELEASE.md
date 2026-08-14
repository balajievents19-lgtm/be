# Production Release — Workspace

This workspace (`C:\laragon\www\be`) contains two nested release repositories:

| Component | Directory | Authoritative git remote |
|-----------|-----------|--------------------------|
| Backend | `backend/` | `be-backend.git` — see [`backend/PRODUCTION_RELEASE.md`](./backend/PRODUCTION_RELEASE.md) |
| Frontend | `frontend/` | `be.git` — see [`frontend/PRODUCTION_RELEASE.md`](./frontend/PRODUCTION_RELEASE.md) |

The **root** git repo currently has **no commits** and must not be used as the production pin source. Nested `.git` directories must stay in place.

Canonical release continuity doc: **[`backend/PRODUCTION_RELEASE.md`](./backend/PRODUCTION_RELEASE.md)**.
