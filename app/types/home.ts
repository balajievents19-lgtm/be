import type { GalleryItem } from '~/types/gallery'
import type { Service } from '~/types/service'

export interface SiteSettings {
  company: {
    name: string | null
    tagline: string | null
    description: string | null
  }
  about: {
    description: string | null
    image: string | null
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

export interface EventOverviewItem {
  id: number
  title: string
  caption: string | null
  description: string | null
  image: string
  link_url: string | null
  featured: boolean
  homepage_featured: boolean
  sort_order: number
}

export interface TestimonialItem {
  id: number
  type: string | null
  name: string
  quote: string | null
  body: string | null
  avatar: string | null
  image: string | null
  featured: boolean
  homepage_featured: boolean
  sort_order: number
}

export interface BlogPostItem {
  id: number
  title: string
  slug: string
  excerpt: string | null
  content?: string | null
  featured_image: string | null
  thumbnail: string | null
  alt_text: string | null
  featured: boolean
  homepage_featured: boolean
  published_at: string | null
  reading_time: number | null
  author: string | null
  tags: string[]
  category?: {
    id: number
    name: string
    slug: string
  } | null
  seo?: {
    title: string | null
    description: string | null
    keywords: string | null
    canonical_url: string | null
    opengraph_image: string | null
    schema_type: string | null
  }
}

export interface FaqItem {
  id: number
  question: string
  slug: string
  answer: string
  featured: boolean
  homepage_featured: boolean
  sort_order: number
  category?: {
    id: number
    name: string
    slug: string
  } | null
}

export interface HomePayload {
  settings: SiteSettings
  hero: HeroSlide[]
  featured_services: Service[]
  events_overview: EventOverviewItem[]
  featured_gallery: GalleryItem[]
  testimonials: TestimonialItem[]
  success_stories: TestimonialItem[]
  featured_blog: BlogPostItem[]
  featured_faqs: FaqItem[]
  faq_schema?: Record<string, unknown> | null
}

export interface HomeApiResponse {
  data: HomePayload
}

export const emptySettings = (): SiteSettings => ({
  company: { name: null, tagline: null, description: null },
  about: { description: null, image: null },
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
})

export const emptyHome = (): HomePayload => ({
  settings: emptySettings(),
  hero: [],
  featured_services: [],
  events_overview: [],
  featured_gallery: [],
  testimonials: [],
  success_stories: [],
  featured_blog: [],
  featured_faqs: [],
  faq_schema: null
})
