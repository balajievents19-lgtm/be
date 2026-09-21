# Balaji Royal Events — Internal Production Audit

**Date:** 2026-09-20  
**Scope:** Existing Nuxt 4 + Laravel 13 / Filament 4 codebase (this repository only).  
**Not in scope:** Mangalam Events, Fageriya, mobile app.

Docs such as `PROJECT_UNDERSTANDING.md` (2026-08-04) are **stale**. This audit is from live code.

---

## Stack (verified)

| Layer | Actual |
|-------|--------|
| Frontend | Nuxt `^4.5.1`, Vue 3, TypeScript, Nuxt UI 4, Tailwind 4, Swiper 14 |
| Backend | Laravel `^13.8`, PHP `^8.3`, Filament `~4.0`, Sanctum, Socialite, Spatie Permission |
| Admin | `/admin` |
| Public API | `/api/*` JSON |

---

## What is already working

- Homepage aggregator `GET /api/home` (cached) with hero, services, events overview, gallery, testimonials, blog, FAQs, stats, CTAs, team, offices
- Services, packages, gallery categories/items, external media, blog, FAQ (list + detail)
- Contact CRM `POST /api/contact` (honeypot, rate limit, slider fields, mass-assignment protection)
- Newsletter, geo search/reverse (throttled)
- Customer auth (register/login/logout/me, forgot/reset, Google/Facebook OAuth stubs)
- Customer vs Filament isolation (`DenyCustomerAdminAccess`, Sanctum `customer` guard)
- Gallery original-file protection + authenticated download
- Filament CMS for most website modules + CRM + RBAC
- SEO resolve API, sitemap.xml, robots.txt from Laravel
- ContentCache + observers
- Security headers, CORS, SSR trusted-read limiter

---

## Issues

### P0 — must fix before production

| ID | File/path | Problem | Why | Recommended fix | Risk | Status |
|----|-----------|---------|-----|-----------------|------|--------|
| P0-1 | `frontend/app/components/home/Hero.vue` | Hero H1/subtitle hardcoded; CMS `title`/`subtitle`/`button_*` ignored | Admin cannot edit hero copy; slides do not match CMS | Render per-slide CMS heading, highlight, subtitle, CTAs | Low | **Fixed** |
| P0-2 | `frontend/app/components/home/Hero.vue` | Hero visual treatment is dated; no dots; weak overlay/typography | Conversion + brand quality | Premium full-width slider (gold/orange, dots, arrows, CLS-safe height) | Low | **Fixed** |
| P0-3 | `frontend/app/components/home/HeroSearch.vue` | CTA says “Search Now” but posts a CRM lead | Misleading UX | CMS `hero_search_button_label` with fallback “Get a Free Quote” | Low | **Fixed** |
| P0-4 | Hero enquiry validation | Weak phone checks; slider date/location only required client-side | Bad CRM data | Align client + `StoreContactInquiryRequest` | Low | **Fixed** |
| P0-5 | Google Reviews | No GBP/Places integration; CMS testimonials only | Requirement for real Google reviews | Backend Places fetch + cache + admin status; no fake reviews | Med | **Fixed (architecture; live when credentials exist)** |
| P0-6 | `database/seeders/AdminUserSeeder.php` + `DatabaseSeeder.php` | Demo admin password + demo CMS seeded on any `db:seed` | Production credential leak / fake content | Skip demo + default admin in `production` | Med | **Fixed** |
| P0-7 | Branding | Fallback strings “Balaji Royal Events”; product is Balaji Royal Events | Brand mix | Fallback `Brand::NAME`; logo files unchanged | Low | **Fixed (fallbacks; existing DB name is CMS)** |
| P0-8 | Lead alerts | New enquiries only appear in Filament | Missed leads if inbox not watched | Queued/sync mail notification to settings email | Low | **Fixed** |

### P1 — important

| ID | File/path | Problem | Why | Recommended fix | Risk | Status |
|----|-----------|---------|-----|-----------------|------|--------|
| P1-1 | Homepage | `featured_faqs`, `cta_sections`, `statistics` unused in `index.vue` | Weaker conversion journey | Render CMS FAQ, stats, CTA | Low | **Fixed** |
| P1-2 | Events | No `/events` route; overview homepage-only | Nav/SEO expectation | Public listing from existing EventOverview API | Low | **Fixed** |
| P1-3 | Legal | No terms page; privacy is thin static | Required legal surfaces | `/terms` + stronger privacy copy (no invented law) | Low | **Fixed** |
| P1-4 | SEO | `/media`, stubs, privacy skip `usePageSeo`; sitemap missing events/media/terms | Incomplete meta/canonical | Resolve types + sitemap static pages | Low | **Fixed** |
| P1-5 | Hero video | Ignores `prefers-reduced-motion` | A11y | Disable video autoplay when reduced motion | Low | **Fixed** |
| P1-6 | FAQ schema | Empty FAQPage possible | Invalid structured data | Omit FAQPage when no Q/A | Low | **Fixed** |
| P1-7 | Footer | No Privacy/Terms links | Discoverability | Footer legal links | Low | **Fixed** |
| P1-8 | Docs | PROJECT_UNDERSTANDING lists modules as missing that exist | Misleading ops | Update setup/Google/deploy docs | Low | **Fixed** |
| P1-9 | Setting social seeders | `facebook.com/` placeholders | Broken social if seeded | Leave empty unless real URLs exist | Low | **Fixed** |
| P1-10 | Gallery originals | Legacy public originals until command + nginx | Accidental original download | Document + keep existing secure command | Ops | **Documented** |

### P2 — improvement

| ID | File/path | Problem | Why | Status |
|----|-----------|---------|-----|--------|
| P2-1 | `frontend/app/data/home.ts` | Dead Lorem / John Doe | Confusion if re-wired | Left unused; do not reconnect |
| P2-2 | No `@nuxt/image` | Unoptimized `<img>` | CWV | Not added (stack change); keep picture/lazy |
| P2-3 | `trustProxies: '*'` | IP spoof if app not behind proxy | Rate-limit bypass | Document for production nginx |
| P2-4 | No HSTS/CSP | Hardening | Document; CSP omitted intentionally today | Remaining |
| P2-5 | Vendor/invite/careers | Stub pages | Honest “contact us” CTAs | Left; not fake product pages |
| P2-6 | Thin customer `/account` | Minimal profile | Future app | Out of website-live critical path |
| P2-7 | Personal access tokens unused | Sanctum table without HasApiTokens | Future mobile tokens | Leave for mobile phase |
| P2-8 | Statistics CMS | Could contain unverifiable claims | Content QA | Flag for owner confirmation |

### P3 — optional later

- Nuxt Image / CDN pipeline  
- Google review *replies* via GBP OAuth (scaffolded, not live)  
- Mobile app (explicitly later)  
- Mangalam Events separate project  

---

## Google Reviews status (architecture)

**Not live until** `GOOGLE_PLACES_API_KEY` + Place ID are set and a successful fetch is confirmed.  
No fake reviews, no fake credentials, no frontend secrets.
