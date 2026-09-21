import type { GalleryCategory, GalleryItem, GalleryListResponse } from '~/types/gallery'
import type { HomePayload } from '~/types/home'
import { isNotFoundError } from '~/utils/httpError'

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
  const route = useRoute()
  const homePayload = useNuxtData<HomePayload>('home')
  const fromHome = homePayload.data.value?.gallery_categories
  const failed = useState('gallery-categories-api-failed', () => false)

  if (route.path === '/' && Array.isArray(fromHome)) {
    failed.value = false

    return {
      data: computed(() => homePayload.data.value?.gallery_categories ?? emptyCategories),
      pending: computed(() => false),
      error: ref(null),
      status: computed(() => 'success' as const),
      refresh: async () => {},
      clear: () => {},
      execute: async () => {},
      failed
    }
  }

  const base = useApiBase()

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
export const useGalleryCategory = async (slug: string) => {
  const base = useApiBase()
  const failed = useState(`gallery-category-api-failed-${slug}`, () => false)

  const asyncData = await useAsyncData(
    `gallery-category-${slug}`,
    async () => {
      try {
        const response = await laravelFetch<GalleryCategoryDetailResponse>(`${base}/gallery/categories/${slug}`)
        failed.value = false
        return response.data ?? null
      } catch (error) {
        if (isNotFoundError(error)) {
          return null
        }
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

  return {
    ...asyncData,
    failed
  }
}
