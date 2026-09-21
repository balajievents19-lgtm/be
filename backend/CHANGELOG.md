# Changelog

All notable changes to the Balaji Royal Events backend (Laravel API + Filament admin).

## [1.0.0] — 2026-08-04

### Added

- Public content APIs: home aggregator, settings, services, gallery, blog, FAQs, event overviews, testimonials
- Public writes: contact enquiries and newsletter subscribe (throttled + honeypot)
- Filament CMS for website sections, CRM, system settings, SEO, users
- Global SEO engine: meta, JSON-LD, sitemap.xml, robots.txt
- Content cache with observers; detail-endpoint caching
- Feature tests for critical API paths and admin panel allowlist
- Production deploy artifacts under `deploy/` plus `DEPLOYMENT.md` and `PRODUCTION_CHECKLIST.md`

### Security

- Filament access gated by `ADMIN_EMAILS` in production
- Upload MIME allowlists on Filament media fields
- Contact mass-assignment limited to safe public fields

### Notes

- Requires PHP 8.3+, MySQL 8+, Node LTS for the sibling Nuxt frontend
- Set `SITE_URL` / `FRONTEND_URL` for correct canonicals and sitemap locs
