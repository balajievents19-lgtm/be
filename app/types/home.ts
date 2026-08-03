import type { Service } from '~/types/service'

export interface SiteSettings {
  company: {
    name: string | null
    tagline: string | null
    description: string | null
  }
  brand: {
    logo: string | null
    dark_logo: string | null
    footer_logo: string | null
    favicon: string | null
  }
  contact: {
    phone: string | null
    alternate_phone: string | null
    whatsapp: string | null
    email: string | null
    support_email: string | null
    address: string | null
    google_map_embed: string | null
  }
  social: {
    facebook: string | null
    instagram: string | null
    youtube: string | null
    linkedin: string | null
    twitter: string | null
  }
  business: {
    working_hours: string | null
    holiday_text: string | null
    emergency_contact: string | null
  }
  seo: {
    meta_title: string | null
    meta_description: string | null
    meta_keywords: string | null
    opengraph_image: string | null
    robots: string | null
    canonical_url: string | null
    google_analytics_id: string | null
    google_search_console_verification: string | null
    facebook_pixel_id: string | null
  }
  theme: {
    primary_color: string | null
    secondary_color: string | null
    theme_mode: string | null
  }
  footer: {
    about: string | null
    copyright_text: string | null
  }
}

export interface HeroSlide {
  id: number
  title: string | null
  subtitle: string | null
  button_text: string | null
  button_url: string | null
  desktop_image: string
  mobile_image: string | null
  video_url: string | null
  overlay_opacity: number
  text_alignment: string
  sort_order: number
}

export interface HomePayload {
  settings: SiteSettings
  hero: HeroSlide[]
  featured_services: Service[]
}

export interface HomeApiResponse {
  data: HomePayload
}
