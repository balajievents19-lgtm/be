import type { FaqItem } from '~/types/home'
import { isNotFoundError } from '~/utils/httpError'

interface FaqsApiResponse {
  data: FaqItem[]
  schema?: Record<string, unknown> | null
}

interface FaqDetailApiResponse {
  data: FaqItem
  schema?: Record<string, unknown> | null
}

export const useFaqs = () => {
  const base = useApiBase()
  const failed = useState('faqs-api-failed', () => false)
  const schema = useState<Record<string, unknown> | null>('faqs-schema', () => null)

  const asyncData = useAsyncData(
    'faqs',
    async () => {
      try {
        const response = await laravelFetch<FaqsApiResponse>(`${base}/faqs`)
        failed.value = false
        schema.value = response.schema ?? null
        return response.data ?? []
      } catch {
        failed.value = true
        return [] as FaqItem[]
      }
    },
    {
      ...useLaravelFetchDefaults<FaqItem[]>(),
      server: true,
      default: (): FaqItem[] => []
    }
  )

  return Object.assign(asyncData, {
    schema,
    failed
  }) as typeof asyncData & {
    schema: typeof schema
    failed: typeof failed
  }
}

/** Single FAQ from GET /api/faqs/{slug}. */
export const useFaq = async (slug: string) => {
  const base = useApiBase()
  const failed = useState(`faq-api-failed-${slug}`, () => false)
  const schema = useState<Record<string, unknown> | null>(`faq-schema-${slug}`, () => null)

  const asyncData = await useAsyncData(
    `faq-${slug}`,
    async () => {
      try {
        const response = await laravelFetch<FaqDetailApiResponse>(`${base}/faqs/${slug}`)
        failed.value = false
        schema.value = response.schema ?? null
        return response.data ?? null
      } catch (error) {
        if (isNotFoundError(error)) {
          throw createError({ statusCode: 404, statusMessage: 'FAQ not found', fatal: true })
        }
        failed.value = true
        return null
      }
    },
    {
      ...useLaravelFetchDefaults<FaqItem | null>(),
      server: true,
      default: (): FaqItem | null => null
    }
  )

  return {
    ...asyncData,
    schema,
    failed
  }
}
