import type { BlogPostItem } from '~/types/home'
import { isNotFoundError } from '~/utils/httpError'

interface BlogListApiResponse {
  data: BlogPostItem[]
}

interface BlogDetailApiResponse {
  data: BlogPostItem
}

export const useBlog = async () => {
  const base = useApiBase()
  const failed = useState('blog-api-failed', () => false)

  const asyncData = await useAsyncData(
    'blog',
    async () => {
      try {
        const response = await laravelFetch<BlogListApiResponse>(`${base}/blog`)
        failed.value = false
        return response.data ?? []
      } catch {
        failed.value = true
        return [] as BlogPostItem[]
      }
    },
    {
      ...useLaravelFetchDefaults<BlogPostItem[]>(),
      server: true,
      default: (): BlogPostItem[] => []
    }
  )

  return {
    ...asyncData,
    failed
  }
}

export const useBlogPost = async (slug: string) => {
  const base = useApiBase()
  const failed = useState(`blog-post-failed-${slug}`, () => false)

  const asyncData = await useAsyncData(
    `blog-${slug}`,
    async () => {
      try {
        const response = await laravelFetch<BlogDetailApiResponse>(`${base}/blog/${slug}`)
        failed.value = false
        return response.data ?? null
      } catch (error) {
        if (isNotFoundError(error)) {
          throw createError({ statusCode: 404, statusMessage: 'Blog post not found', fatal: true })
        }
        failed.value = true
        return null
      }
    },
    {
      ...useLaravelFetchDefaults<BlogPostItem | null>(),
      server: true,
      default: (): BlogPostItem | null => null
    }
  )

  return {
    ...asyncData,
    failed
  }
}
