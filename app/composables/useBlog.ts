import type { BlogPostItem } from '~/types/home'

interface BlogListApiResponse {
  data: BlogPostItem[]
}

interface BlogDetailApiResponse {
  data: BlogPostItem
}

export const useBlog = async () => {
  const base = useApiBase()
  const failed = useState('blog-api-failed', () => false)

  const asyncData = await useFetch(`${base}/blog`, {
    key: 'blog',
    server: true,
    default: (): BlogPostItem[] => [],
    transform: (response: BlogListApiResponse): BlogPostItem[] => response.data ?? [],
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

export const useBlogPost = async (slug: string) => {
  const base = useApiBase()
  const failed = useState(`blog-post-failed-${slug}`, () => false)

  const asyncData = await useFetch(`${base}/blog/${slug}`, {
    key: `blog-${slug}`,
    server: true,
    default: (): BlogPostItem | null => null,
    transform: (response: BlogDetailApiResponse): BlogPostItem => response.data,
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
