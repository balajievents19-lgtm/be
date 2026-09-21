# Changelog

All notable changes to the Balaji Royal Events frontend (Nuxt SSR).

## [1.0.0] — 2026-08-04

### Added

- CMS-driven homepage via `/api/home` (hero, about, services, events, gallery, testimonials, news)
- Pages: About, Services (+ detail), Gallery, Blog (+ detail), FAQ, Contact
- Contact and newsletter forms wired to public API (honeypot)
- Settings-driven SEO meta, Open Graph, Twitter card, and canonical links
- FAQ JSON-LD on homepage when provided by API
- Playwright smoke tests (desktop + mobile)
- Production env example and deployment notes

### Notes

- Requires sibling Laravel API (`NUXT_PUBLIC_API_BASE`)
- Logo falls back to `/images/logo.png` when CMS logo unset
- Stub “Coming Soon” routes remain for top-bar links (vendor, invite, login, register, etc.)
