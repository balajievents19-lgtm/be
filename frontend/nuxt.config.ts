// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui'
  ],

  // Bootstrap master is light-only — prevent Nuxt UI dark mode (slate-900) from restyling sections
  ui: {
    colorMode: false
  },

  // Devtools are stripped from production builds by Nuxt.
  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    // Server-only: Nuxt SSR → Laravel trusted read header (NUXT_SSR_INTERNAL_SECRET).
    // Must match backend SSR_INTERNAL_SECRET. Never put this under `public`.
    ssrInternalSecret: '',
    // Server-only Laravel origin for SSR (NUXT_API_INTERNAL_BASE).
    // Production: http://127.0.0.1:8000/api — never expose this to the browser.
    apiInternalBase: '',
    public: {
      // Browser API prefix. Production always resolves to same-origin /api
      // (Nginx → Laravel). Local .env.example may set http://localhost:8000/api
      // for `nuxt dev`. Do not bake 127.0.0.1 into a production client bundle.
      apiBase: ''
    }
  },

  // Homepage is CMS-driven via GET /api/home — always SSR so a down API
  // cannot bake "temporarily unavailable" / empty sections into static HTML.
  routeRules: {
    '/': { prerender: false },
    '/**': {
      headers: {
        'X-Content-Type-Options': 'nosniff',
        'X-Frame-Options': 'SAMEORIGIN',
        'Referrer-Policy': 'strict-origin-when-cross-origin',
        'Permissions-Policy': 'camera=(), microphone=(), geolocation=(self)'
      }
    }
  },

  compatibilityDate: '2026-06-30',

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  }
})
