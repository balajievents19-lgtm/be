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

export interface GalleryItemEmbed {
  embed_url: string | null
  open_url: string | null
  mode: 'embed' | 'link'
  poster_url: string | null
  cta_label: string | null
}

export interface GalleryItem {
  id: number
  title: string
  slug: string
  media_type?: 'image' | 'video' | string
  description?: string | null
  image: string | null
  thumbnail: string | null
  alt_text: string | null
  caption: string | null
  youtube_url: string | null
  vimeo_url: string | null
  video_source?: string | null
  video_id?: string | null
  video_url?: string | null
  embed?: GalleryItemEmbed | null
  featured: boolean
  homepage_featured: boolean
  sort_order: number
  download_available?: boolean
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
export const isGalleryVideo = (item: GalleryItem): boolean => item.media_type === 'video'

export const galleryItemSrc = (item: GalleryItem): string =>
  item.thumbnail || item.image || item.embed?.poster_url || ''

export const galleryItemAlt = (item: GalleryItem): string =>
  item.alt_text || item.title || (isGalleryVideo(item) ? 'Gallery video' : 'Gallery image')
