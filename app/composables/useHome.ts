import type { HomeApiResponse, HomePayload } from '~/types/home'

const emptyHome: HomePayload = {
  settings: {
    company: { name: null, tagline: null, description: null },
    brand: { logo: null, dark_logo: null, footer_logo: null, favicon: null },
    contact: {
      phone: null,
      alternate_phone: null,
      whatsapp: null,
      email: null,
      support_email: null,
      address: null,
      google_map_embed: null
    },
    social: {
      facebook: null,
      instagram: null,
      youtube: null,
      linkedin: null,
      twitter: null
    },
    business: {
      working_hours: null,
      holiday_text: null,
      emergency_contact: null
    },
    seo: {
      meta_title: null,
      meta_description: null,
      meta_keywords: null,
      opengraph_image: null,
      robots: null,
      canonical_url: null,
      google_analytics_id: null,
      google_search_console_verification: null,
      facebook_pixel_id: null
    },
    theme: {
      primary_color: null,
      secondary_color: null,
      theme_mode: null
    },
    footer: {
      about: null,
      copyright_text: null
    }
  },
  hero: [],
  featured_services: []
}

/** Single homepage aggregator request: settings + hero + featured services. */
export const useHome = async () => {
  const config = useRuntimeConfig()
  const base = String(config.public.apiBase || 'http://127.0.0.1:8000/api').replace(/\/$/, '')
  const failed = useState('home-api-failed', () => false)

  const asyncData = await useFetch(`${base}/home`, {
    key: 'home',
    server: true,
    default: (): HomePayload => emptyHome,
    transform: (response: HomeApiResponse): HomePayload => response.data,
    onResponse: () => {
      failed.value = false
    },
    onResponseError: () => {
      failed.value = true
    },
    onRequestError: () => {
      failed.value = true
    }
  })

  return {
    ...asyncData,
    failed
  }
}
