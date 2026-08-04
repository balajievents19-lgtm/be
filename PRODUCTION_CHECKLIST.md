# Production checklist — Frontend (Nuxt)

Companion to backend `PRODUCTION_CHECKLIST.md`.

- [ ] `NUXT_PUBLIC_API_BASE` set to production API `/api` **before** build
- [ ] `pnpm install --frozen-lockfile` succeeds
- [ ] `pnpm lint` — no errors
- [ ] `pnpm typecheck` — pass
- [ ] `pnpm build` — pass
- [ ] Nuxt bound to `127.0.0.1:3000` (not public `0.0.0.0` without firewall)
- [ ] Supervisor `balaji-events-nuxt` running
- [ ] Nginx proxies `www` host to Nuxt
- [ ] Homepage SSR shows CMS content (API reachable from Node host)
- [ ] About / Services / Gallery / Blog / FAQ / Contact load
- [ ] Contact + newsletter forms succeed against production API
- [ ] Mobile smoke (Playwright mobile project or device check)
- [ ] No mixed-content (HTTPS site → HTTPS API)
