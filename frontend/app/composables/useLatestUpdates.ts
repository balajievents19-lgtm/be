import type { BlogPostItem, HomePayload } from '~/types/home'

interface BlogListApiResponse {
  data: BlogPostItem[]
}

/**
 * Footer Latest Updates — reuses homepage featured_blog or the shared `blog` cache.
 */
export const useLatestUpdates = (options?: { enabled?: boolean }) => {
  const homePayload = useNuxtData<HomePayload>('home')
  const fromHome = computed(() => homePayload.data.value?.featured_blog ?? [])
  const enabled = options?.enabled !== false
  const needsBlogFetch = enabled && fromHome.value.length === 0

  const asyncData = useAsyncData(
    'blog',
    async () => {
      const base = useApiBase()
      const response = await laravelFetch<BlogListApiResponse>(`${base}/blog`)
      return response.data ?? []
    },
    {
      ...useLaravelFetchDefaults<BlogPostItem[]>(),
      server: true,
      default: (): BlogPostItem[] => [],
      immediate: needsBlogFetch
    }
  )

  const updates = computed(() => {
    if (fromHome.value.length > 0) {
      return fromHome.value.slice(0, 3)
    }
    return (asyncData.data.value ?? []).slice(0, 3)
  })

  return {
    ...asyncData,
    updates
  }
}
