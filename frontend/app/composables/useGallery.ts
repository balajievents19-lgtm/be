import type { GalleryCategory, GalleryItem, GalleryListResponse } from '~/types/gallery'

const emptyList: GalleryItem[] = []
const emptyCategories: GalleryCategoryCard[] = []

export interface GalleryCategoryCard extends GalleryCategory {
  description?: string | null
  sort_order?: number
  image_count?: number
  cover_image?: string | null
}

export interface GalleryCategoryListResponse {
  data: GalleryCategoryCard[]
}

export interface GalleryCategoryDetailResponse {
  data: {
    category: GalleryCategory & { description?: string | null }
    items: GalleryItem[]
  }
}

/** Shared gallery list from GET /api/gallery. */
export const useGallery = () => {
  const base = useApiBase()
  const failed = useState('gallery-api-failed', () => false)

  const asyncData = useAsyncData(
    'gallery',
    async () => {
      try {
        const response = await laravelFetch<GalleryListResponse>(`${base}/gallery`)
        failed.value = false
        return response.data ?? emptyList
      } catch {
        failed.value = true
        return emptyList
      }
    },
    {
      ...useLaravelFetchDefaults<GalleryItem[]>(),
      server: true,
      default: (): GalleryItem[] => emptyList
    }
  )

  return Object.assign(asyncData, {
    failed
  }) as typeof asyncData & { failed: typeof failed }
}

/** Category-first gallery landing from GET /api/gallery/categories. */
export const useGalleryCategories = () => {
  const base = useApiBase()
  const failed = useState('gallery-categories-api-failed', () => false)

  const asyncData = useAsyncData(
    'gallery-categories',
    async () => {
      try {
        const response = await laravelFetch<GalleryCategoryListResponse>(`${base}/gallery/categories`)
        failed.value = false
        return response.data ?? emptyCategories
      } catch {
        failed.value = true
        return emptyCategories
      }
    },
    {
      ...useLaravelFetchDefaults<GalleryCategoryCard[]>(),
      server: true,
      default: (): GalleryCategoryCard[] => emptyCategories
    }
  )

  return Object.assign(asyncData, {
    failed
  }) as typeof asyncData & { failed: typeof failed }
}

/** Category detail from GET /api/gallery/categories/{slug}. */
export const useGalleryCategory = (slug: string) => {
  const base = useApiBase()
  const failed = useState(`gallery-category-api-failed-${slug}`, () => false)

  const asyncData = useAsyncData(
    `gallery-category-${slug}`,
    async () => {
      try {
        const response = await laravelFetch<GalleryCategoryDetailResponse>(`${base}/gallery/categories/${slug}`)
        failed.value = false
        return response.data ?? null
      } catch {
        failed.value = true
        return null
      }
    },
    {
      ...useLaravelFetchDefaults<GalleryCategoryDetailResponse['data'] | null>(),
      server: true,
      default: (): GalleryCategoryDetailResponse['data'] | null => null
    }
  )

  return Object.assign(asyncData, {
    failed
  }) as typeof asyncData & { failed: typeof failed }
}
