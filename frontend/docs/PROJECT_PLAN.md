# Balaji Royal Events — Implementation Plan

## Purpose

Modernize the Balaji Royal Events frontend with Nuxt 4, Nuxt UI 4, Tailwind CSS 4, and TypeScript while preserving `reference/bootstrap-master` as the master design.

## Non-negotiable rules

- Preserve the master layout, section order, navigation, branding, colors, and visual flow.
- Do not redesign or introduce substitute layouts.
- Improve only code quality, responsiveness, accessibility, performance, and reusable architecture.
- Use reusable Vue components, Nuxt UI 4 where appropriate, Tailwind CSS 4, and TypeScript.
- Keep the project runnable throughout the work.
- After every implementation phase, lint modified files, run typecheck, run a production build, and fix issues found.

## Master design inventory

### Supplied HTML pages

| Master file | Nuxt route | Notes |
|---|---|---|
| `index.html` | `/` | Homepage source of truth |
| `about.html` | `/about` | About route shell; its content is contact-style in the supplied source |
| `contact.html` | `/contact` | Contact page source of truth |

### Homepage section order

1. Header: top utility bar and main navigation
2. Login and Registration modals
3. Hero banner slider
4. Hero search form
5. Our Services
6. Events Overview
7. Client Say’s
8. Success Story
9. Share Your Story
10. Latest News
11. Footer

## Current implementation status

| Area | Status | Notes |
|---|---|---|
| Header | Implemented | Shared header, top bar, navigation, mega menu, search expansion, and a single mobile drawer |
| Hero | Implemented | Three master banner images, centered heading, slider controls, responsive layout |
| Hero Search | Implemented | Event type, location, date, and search flow |
| Services | Partial | Correct content foundation; needs master-accurate spacing and styling audit |
| Events Overview | Partial | Renders but needs refactor from current grid to the master carousel layout |
| Testimonials | Missing | Component exists but is empty |
| Success Story | Missing | Component exists but is empty |
| Latest News | Missing | Component exists but is empty |
| Footer | Missing | Component exists but is empty |
| About | Missing | Current route is a placeholder |
| Contact | Missing | Current route is a placeholder |
| Gallery | Missing | Current route is a placeholder; no standalone master HTML exists |

## Component architecture

### Existing canonical components

- `app/components/layout/AppHeader.vue`
- `app/components/layout/AppTopBar.vue`
- `app/components/layout/AppNavbar.vue`
- `app/components/layout/AppMegaMenu.vue`
- `app/components/layout/AppSearchPopup.vue`
- `app/components/layout/AppMobileMenu.vue`
- `app/components/home/Hero.vue`
- `app/components/home/HeroSearch.vue`

### Components to create or complete

| Master section | Target Vue component |
|---|---|
| Master heading with icon and horizontal rule | `home/SectionHeading.vue` |
| Events Overview carousel | `home/EventsOverview.vue` |
| Client Say’s carousel | `home/Testimonials.vue` |
| Success Story list | `home/SuccessStory.vue` |
| Share Your Story form | `home/ShareStoryForm.vue` |
| Latest News cards | `home/LatestNews.vue` |
| Shared footer | `layout/AppFooter.vue` |
| Internal page title background | `shared/PageHeader.vue` |
| Breadcrumb trail | `shared/Breadcrumbs.vue` |
| Contact detail cards | `contact/ContactInfoCards.vue` |
| Contact form | `contact/ContactForm.vue` |
| Login modal | `layout/LoginModal.vue` |
| Registration modal | `layout/RegisterModal.vue` |

### Duplicate or legacy components

- `app/components/home/HeroSection.vue`
- `app/components/home/HeroSlider.vue`

These overlap with the canonical `Hero.vue`. Confirm there are no imports, then retire or consolidate them without leaving duplicate Hero implementations.

## Asset migration plan

### Already available

- Logo
- Three hero slider images
- Hero heading background graphic
- Three event images
- Local icon CSS and font assets

### Required for later phases

| Master assets | Destination use |
|---|---|
| `parallax-img/friend-infoBg.jpg` | Testimonials background |
| `user-img/friend-img.png`, `img-fream.png` | Testimonial profile frame |
| `starting-point.png`, `ending-point.png` | Testimonial quote decoration |
| `user-img/story-img1.png`, `story-img2.png` | Success Story entries |
| `news-img/` and homepage news images | Latest News |
| `event-img/update-img1.png` through `update-img3.png` | Footer updates |
| `pageHeadBg.jpg` | About and Contact page headings |
| `about-us/` | About page |
| `gallery-img/` | Gallery route |

### Typography and icon policy

- Preserve master font hierarchy: Open Sans for UI copy, Domine for emphasized headings, Roboto where used.
- Preserve the custom master icon font and its icon names.
- Prefer local font files or safe fallbacks for production reliability; do not require runtime third-party font downloads.

## Implementation phases

### Phase 1 — Header

Status: complete.

- Master top bar, navigation, services mega menu, sticky shrink behavior, search expansion, active links, keyboard access, and one mobile drawer.

### Phase 2 — Hero and Hero Search

Status: complete.

- Master three-slide Hero, centered title, decorative graphic, search card, desktop arrows, mobile reflow, and accessible Swiper behavior.

### Phase 3 — Services

- Keep the existing ten services and master ordering.
- Match the master five-column desktop grid and responsive reductions.
- Match section spacing, heading divider, cards, icon scale, and orange hover state.
- Keep every card linked to the Services route.

### Phase 4 — About

- Build `/about` strictly from the supplied `about.html` structure.
- Add shared internal page heading and breadcrumb components.
- Reuse Header and Footer.
- Preserve the supplied source content until replacement content is provided.

### Phase 5 — Events Overview / Event Categories

- Refactor `EventsOverview.vue` to the master carousel structure.
- Use master event images and titles.
- Preserve caption hover overlay, typography, arrow placement, and responsive item counts.
- Avoid the current redesigned card-grid treatment.

### Phase 6 — Gallery

- Build `/gallery` from available master gallery assets and shared master styles only.
- Do not invent a new visual language because no standalone gallery HTML is supplied.
- Use accessible image controls and a progressive enhancement lightbox when implemented.

### Phase 7 — Testimonials

- Build Client Say’s with the master dark background, heading treatment, framed avatar, quote decorations, and carousel behavior.
- Support keyboard controls, reduced motion, and responsive text sizing.

### Phase 8 — Success Story and Share Your Story

- Build the two-column desktop composition and stacked mobile layout.
- Add typed client-side validation and Laravel API-ready submit boundaries.
- Use accessible labels, file input handling, and loading/error states.

### Phase 9 — Latest News

- Recreate the master four-card composition, including the variant card spacing and image/text combinations.
- Keep the section background, heading treatment, and CTA styles consistent with the master.

### Phase 10 — Footer

- Implement a reusable `AppFooter`.
- Include latest updates, company links, contact details, newsletter form, social links, and copyright strip.
- Reuse it on Home, About, and Contact.

### Phase 11 — Contact and shared-page completion

- Implement master contact detail cards and contact form at `/contact`.
- Integrate shared PageHeader, Header, and Footer.
- Define Laravel API-ready form interfaces without inventing backend endpoints.

## Responsive and interaction requirements

- Preserve master breakpoints and content flow around 1399px, 1199px, 991px, and 767px.
- Use responsive Tailwind classes instead of Bootstrap styles.
- Replace jQuery/Owl behavior with Vue/Swiper where a carousel is required.
- Replace jQuery datepicker with an accessible Nuxt UI-compatible date input.
- Preserve sticky header, search reveal, slider controls, hover states, mobile navigation, and form behavior.
- Respect reduced-motion preferences and ensure keyboard access to all interactive controls.

## Quality gates for every phase

1. Compare the completed area against the corresponding master HTML/CSS/assets.
2. Check desktop and mobile behavior in the browser.
3. Run ESLint on every modified Vue/TypeScript file.
4. Run `pnpm typecheck`.
5. Run `pnpm build`.
6. Fix all project changes caused by the phase before starting the next phase.

## Final verification checklist

- Homepage section order matches `index.html` exactly.
- No duplicate mobile menus or Hero implementations remain.
- Header, Hero, forms, sliders, links, and menus work with keyboard input.
- All master images, icons, and typography roles render from stable local assets or approved fallbacks.
- Placeholder routes are replaced only where master reference material exists.
- The application remains lint-clean for modified files, type-safe, and production-buildable.
