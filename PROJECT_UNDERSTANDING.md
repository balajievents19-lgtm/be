# Balaji Events Platform — Project Understanding

**Generated:** 2026-08-04  
**Repos:** `be/frontend` (Nuxt 4) + `be/backend` (Laravel 13 / Filament 4)  
**Mode:** Analysis only — no application code modified except this document and `MASTER_PLAN.md`.

---

## Stack

| Layer | Technology |
|-------|------------|
| Frontend | Nuxt 4, Vue 3, TypeScript, Tailwind 4, Nuxt UI |
| Backend | Laravel 13, PHP 8.3, MySQL |
| Admin | Filament 4 at `/admin` |
| Public API | `/api/*` JSON (mostly read-only) |
| Git | Separate repos for frontend and backend |

---

## 1. Existing Modules

### Backend CMS + API

| Module | Models | Filament | API |
|--------|--------|----------|-----|
| Website Settings | `Setting` (singleton) | SettingResource | `GET /settings`, nested in `/home` |
| Slider | `HeroSlide` | HeroSlideResource | `GET /hero-slides`, nested in `/home` |
| Services | `Service` | ServiceResource | `GET /services`, `GET /services/{slug}`, featured in `/home` |
| Gallery | `GalleryCategory`, `GalleryItem` | Both | `GET /gallery`, `GET /gallery/{slug}`, featured in `/home` |
| Latest News (Blog) | `BlogCategory`, `BlogPost` | Both | `GET /blog`, `GET /blog/{slug}`, featured in `/home` |
| FAQ | `FaqCategory`, `Faq` | Both | `GET /faqs`, `GET /faqs/{slug}`, featured in `/home` |
| Contact CRM | `ContactInquiry` | ContactInquiryResource | `POST /contact` (throttle 5/min) |
| SEO Engine | — (SeoService) | Via settings fields | `GET /seo`, `GET /seo/resolve`; `/sitemap.xml`, `/robots.txt` |
| Homepage aggregator | — | — | `GET /home` (cached) |
| Users | `User` | None | Filament login only |

### Frontend website

| Area | Status |
|------|--------|
| Homepage | Live — API + static mix |
| About | Live — static |
| Services list + detail | Live — API |
| Gallery | Live — API |
| FAQ | Live — mostly static; services names from API |
| Contact | Live — static info; form not wired to API |
| Blog, Careers, Privacy, Auth, Vendor, Invite | Stub / Coming Soon |

### Cross-cutting already built

- `ContentCache` + `ContentCacheObserver` (TTL 300s, CRUD flush)
- `HasPublicStorageUrl` trait
- API rate limit 60/min; contact 5/min
- Production indexes on contact + soft-delete columns
- Soft deletes on services, gallery, blog, FAQs

---

## 2. Missing Modules

| Module | Backend | Frontend | Notes |
|--------|---------|----------|-------|
| About CMS | No dedicated entity | Static `About.vue` | Only `company_description` / `footer_about` |
| Events Overview | No model/API | Static `overviewEvents` | Homepage section |
| Client Says (Testimonials) | **Missing entirely** | Static testimonials / success stories | Often listed as “done” — not in codebase |
| Newsletter | Missing | Footer form UI only | |
| Users admin resource | Model exists | — | No Filament UserResource |
| Roles & Permissions | Not installed | — | Policies allow-all; `canAccessPanel` always true |
| Blog FE pages | API ready | `/blog` stub; no `[slug]` | Homepage Latest News still static |
| FAQ FE ↔ API | API ready | Uses `data/faq.ts` | `featured_faqs` unused on home |
| Contact form → CRM | API ready | Local-only form | Field mismatch (`mobile` required by API) |
| Gallery / FAQ detail routes | API `show` exists | No FE pages | Sitemap may list FAQ slugs |

---

## 3. Frontend Sections

### Homepage (`app/pages/index.vue`)

| Section | Component | Data |
|---------|-----------|------|
| Hero + Search | `HomeHero`, `HeroSearch` | **Dynamic** — `/home` hero + featured_services |
| Services | `HomeServices` | **Dynamic** — featured_services |
| Events Overview | `HomeEventsOverview` | **Static** — `data/home.ts` |
| Gallery | `HomeGallery` | **Dynamic** — featured_gallery |
| Client Says | `HomeTestimonials` | **Static** — testimonials / successStories |
| Latest News | `HomeLatestNews` | **Static** — latestNews (`featured_blog` unused) |
| Footer | `HomeFooter` | **Hybrid** — settings when present + static links/updates |

### Other pages

| Page | Dynamic | Static |
|------|---------|--------|
| `/services`, `/services/[slug]` | List/detail API | List SEO, CTA, inquiry form offline |
| `/gallery` | Full gallery API | Title/breadcrumbs |
| `/faq` | Services names only | FAQ copy from `data/faq.ts` |
| `/about` | None | About copy + contact cards |
| `/contact` | None | Contact cards + unwired form |
| `/blog` etc. | None | Stubs |

### Composables

- `useApiBase`, `useHome` → `/home`
- `useServices` / `useService` → `/services`, `/services/{slug}` (on `/` reuses home featured)
- `useGallery` → `/gallery`
- **Missing:** contact, faqs, blog, settings, seo, testimonials

---

## 4. Backend Resources (Filament)

| Label (current) | Group | Sort | Resource |
|-----------------|-------|------|----------|
| Website Settings | Website | 0 | SettingResource |
| Hero Slides | Website | 1 | HeroSlideResource |
| Services | Website | 2 | ServiceResource |
| Gallery Categories | Website | 3 | GalleryCategoryResource |
| Gallery | Website | 4 | GalleryItemResource |
| Blog Categories | Website | 5 | BlogCategoryResource |
| Blog | Website | 6 | BlogPostResource |
| FAQ Categories | Website | 7 | FaqCategoryResource |
| FAQs | Website | 8 | FaqResource |
| Inquiries | CRM | 1 | ContactInquiryResource |

**Not present:** About, Events Overview, Client Says, Contact page, Footer page, Newsletter, SEO page, Users, Roles.

**Form UX:** Settings has **8 tabs**; Blog has 4 (General/Media/SEO/Publishing); Services/Gallery/Contact have 3; FAQ has 2; Hero/categories use sections only. No custom empty states. Dashboard still shows `FilamentInfoWidget`.

---

## 5. API Map

Prefix: `/api`

| Method | Path | Controller | Cached |
|--------|------|------------|--------|
| GET | `/home` | HomeController@show | Yes (HOME) |
| GET | `/hero-slides` | HeroSlideController@index | Yes (HERO) |
| GET | `/settings` | SettingController@show | Yes (SETTINGS) |
| GET | `/seo` | SeoController@show | SeoService cache |
| GET | `/seo/resolve` | SeoController@resolve | SeoService cache |
| GET | `/services` | ServiceController@index | Yes (SERVICES) |
| GET | `/services/{slug}` | ServiceController@show | No |
| GET | `/gallery` | GalleryController@index | Yes (GALLERY) |
| GET | `/gallery/{slug}` | GalleryController@show | No |
| GET | `/blog` | BlogController@index | Yes (BLOG) |
| GET | `/blog/{slug}` | BlogController@show | No |
| GET | `/faqs` | FaqController@index | Yes (FAQS) |
| GET | `/faqs/{slug}` | FaqController@show | No |
| POST | `/contact` | ContactController@store | N/A (throttle:contact) |

**Web:** `/sitemap.xml`, `/robots.txt`.

**Home payload keys:** `settings`, `hero`, `featured_services`, `featured_gallery`, `featured_blog`, `featured_faqs`, `faq_schema`.

---

## 6. Database Map

### Tables

| Table | Soft deletes | Notes |
|-------|--------------|-------|
| `users` | No | Admin auth |
| `sessions`, `password_reset_tokens` | — | Framework |
| `cache`, `cache_locks` | — | Framework |
| `jobs`, `job_batches`, `failed_jobs` | — | Framework |
| `settings` | No | Singleton |
| `hero_slides` | No | |
| `services` | Yes | |
| `gallery_categories` | Yes | |
| `gallery_items` | Yes | FK → gallery_categories |
| `blog_categories` | Yes | |
| `blog_posts` | Yes | FK → blog_categories |
| `faq_categories` | Yes | |
| `faqs` | Yes | FK → faq_categories |
| `contact_inquiries` | No | FK → users (`assigned_to`) |

### Relationships

```
Setting (1)
User (1) ──< ContactInquiry (assigned_to)
GalleryCategory (1) ──< GalleryItem
BlogCategory (1) ──< BlogPost
FaqCategory (1) ──< Faq
HeroSlide — standalone
Service — standalone
```

### Indexes (production)

- `contact_inquiries`: `mobile`, `email`
- Soft-delete indexes: `services`, `gallery_items`, `blog_posts`, `faqs`

---

## 7. Duplicate Logic

- `uniqueSlugFrom` copied across Service, Gallery*, Blog*, Faq*
- Repeated `active` / `ordered` / `homepage` scopes
- Image URL: trait vs HeroSlide custom accessors
- FAQ JSON-LD built in HomeResource, FaqController, and SeoService
- Allow-all Filament policies (near-identical stubs)
- Frontend contact facts duplicated (`data/contact.ts`, `data/faq.ts`, `siteContact`, settings)

---

## 8. Duplicate APIs

| Pair | Verdict |
|------|---------|
| `/home` vs module list endpoints | Intentional aggregator vs page APIs — keep both |
| `/settings` vs `home.settings` | OK — reuse home on `/`; settings for other pages |
| `/hero-slides` vs `home.hero` | Low need for separate FE call on home |
| Resource SEO fields vs `/seo` + `/seo/resolve` | Complementary; FE does not call resolve yet |
| No duplicate write APIs | Single `POST /contact` |

**Rule:** Do not add parallel mobile/home endpoints. Extend existing shapes carefully.

---

## 9. Performance Issues

- Home API builds `featured_blog`, `featured_faqs`, `faq_schema` but homepage UI ignores them (wasted work until wired)
- Detail endpoints (`show`) not cached
- FAQ index selects all columns (heavier than other lists)
- Service detail also loads full services list for “related” names (shared cache key mitigates)
- On `/`, menus use featured-only services subset (may be incomplete vs full catalog)
- Some LCP candidates lazy-loaded (e.g. service detail hero)
- Navbar logos lack consistent loading priority hints
- Open CORS (`*`) increases cache/CDN complexity for credentialed scenarios (public API OK for now)

---

## 10. Security Issues

| Issue | Severity | Detail |
|-------|----------|--------|
| `User::canAccessPanel` always true | High | Any DB user can open admin |
| Policies allow-all | High | No roles/permissions |
| ContactInquiry elevated fields fillable | Medium | Mitigated on public store via `only()` + `forceFill` |
| File MIME allowlists incomplete | Low–Med | Most uploads rely on `->image()` only |
| Contact FE not calling API | Med | Fake success UX; no honeypot on FE |
| CORS `allowed_origins: *` | Low | Fine for public read API; tighten for production if needed |
| No `SITE_URL` / `FRONTEND_URL` in `.env.example` | Med | Sitemap/robots may point at wrong host |

---

## 11. SEO Status

### Backend — strong

- SeoService: home, service, blog, gallery, FAQ, static pages
- MetaBuilder + SchemaBuilder (Organization, LocalBusiness, FAQPage, etc.)
- Sitemap + robots controllers
- Per-entity SEO fields on content models + settings

### Frontend — partial

- Home + service detail use some API SEO fields via `useSeoMeta`
- Other pages use hardcoded meta
- **Not used:** `/api/seo`, `/api/seo/resolve`, JSON-LD / `faq_schema`, canonical, robots meta, keywords, GA / Search Console / Pixel, favicon from settings
- Sitemap may advertise `/blog`, `/faq/{slug}`, privacy — FE stubs or missing routes
- `robots.txt` Sitemap URL may use `APP_URL` instead of public site URL

---

## 12. Admin UX Review

| Topic | Current | Target |
|-------|---------|--------|
| Language | Developer labels (Hero Slides, Blog, Inquiries) | Website language (Slider, Latest News, Contact Enquiries) |
| Groups | Website + CRM only | Website, CRM, System, Users |
| Categories in nav | Visible clutter | Hide from nav; manage via relations |
| Settings form | 8 tabs | Max 4: General, Media, SEO, Publish |
| Content forms | Inconsistent tabs | General / Media / SEO / Publish |
| Dashboard | Account + FilamentInfoWidget | Business stats + quick actions |
| Empty states | Defaults only | Business copy |
| Missing nav items | About, Events, Client Says, Newsletter, Users, Roles | Phase work — no stubs without models |

---

## 13. Production Readiness

### Ready

- Core CMS modules and public read APIs
- Contact CRM endpoint + throttle
- Content caching + invalidation
- Soft deletes + production indexes
- SEO backend engine
- Frontend services/gallery/home (partial) integration
- Lint / typecheck / build validated in prior phase

### Not ready for v1.0 “complete”

- [ ] Admin UX aligned to website sections
- [ ] Wire remaining static FE to existing APIs
- [ ] Missing CMS: Testimonials, Events Overview, About (as needed)
- [ ] Contact forms → API
- [ ] Blog + FAQ frontend pages
- [ ] Full SEO wiring (resolve, JSON-LD, analytics, correct site URL)
- [ ] Roles / panel access control
- [ ] Feature, API, and Playwright tests
- [ ] Production ENV, queue, scheduler, nginx/SSL, Cloudflare, backups, monitoring, deploy guide
- [ ] Remove Lorem/placeholder content from `data/home.ts`

### Hard constraints for remaining work

- Do not redesign Bootstrap-matched UI
- Do not break existing API contracts
- Do not duplicate models, APIs, composables, or services
- Prefer reuse of Setting / existing modules before new entities

---

## Key Paths

| Area | Path |
|------|------|
| FE pages | `frontend/app/pages/` |
| FE home sections | `frontend/app/components/home/` |
| FE static data | `frontend/app/data/` |
| FE composables | `frontend/app/composables/` |
| API routes | `backend/routes/api.php` |
| Models | `backend/app/Models/` |
| Filament | `backend/app/Filament/Resources/` |
| Cache | `backend/app/Support/ContentCache.php` |
| SEO | `backend/app/Services/Seo/` |
| Panel | `backend/app/Providers/Filament/AdminPanelProvider.php` |

---

*End of Project Understanding.*
