export interface GalleryCategory {
  id: number
  name: string
  slug: string
}

export interface GalleryItemSeo {
  title: string | null
  description: string | null
  opengraph_image: string | null
}

export interface GalleryItem {
  id: number
  title: string
  slug: string
  description?: string | null
  image: string | null
  thumbnail: string | null
  alt_text: string | null
  caption: string | null
  youtube_url: string | null
  vimeo_url: string | null
  featured: boolean
  homepage_featured: boolean
  sort_order: number
  category?: GalleryCategory | null
  seo?: GalleryItemSeo
}

export interface GalleryListResponse {
  data: GalleryItem[]
}

export interface GalleryDetailResponse {
  data: GalleryItem
}

/** Display helpers shared by homepage + gallery page grids. */
export const galleryItemSrc = (item: GalleryItem): string =>
  item.thumbnail || item.image || ''

export const galleryItemAlt = (item: GalleryItem): string =>
  item.alt_text || item.title || 'Gallery image'
