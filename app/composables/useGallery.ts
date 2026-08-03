import type { GalleryItem, GalleryListResponse } from '~/types/gallery'

const emptyList: GalleryItem[] = []

/** Shared gallery list from GET /api/gallery. */
export const useGallery = async () => {
  const base = useApiBase()
  const failed = useState('gallery-api-failed', () => false)

  const asyncData = await useFetch(`${base}/gallery`, {
    key: 'gallery',
    server: true,
    default: (): GalleryItem[] => emptyList,
    transform: (response: GalleryListResponse): GalleryItem[] => response.data ?? emptyList,
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
