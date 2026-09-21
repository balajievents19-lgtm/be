export interface ServiceSeo {
  title: string | null
  description: string | null
  keywords: string | null
  opengraph_image: string | null
}

export interface Service {
  id: number
  name: string
  slug: string
  short_description: string | null
  full_description?: string | null
  featured_image: string | null
  banner_image?: string | null
  gallery_images?: string[]
  related_gallery?: import('~/types/gallery').GalleryItem[]
  icon: string | null
  featured: boolean
  show_on_homepage: boolean
  sort_order: number
  seo?: ServiceSeo
}

export interface ServiceListResponse {
  data: Service[]
}

export interface ServiceDetailResponse {
  data: Service
}
