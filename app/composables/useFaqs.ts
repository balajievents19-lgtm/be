import type { FaqItem } from '~/types/home'

interface FaqsApiResponse {
  data: FaqItem[]
}

export const useFaqs = async () => {
  const base = useApiBase()
  const failed = useState('faqs-api-failed', () => false)

  const asyncData = await useFetch(`${base}/faqs`, {
    key: 'faqs',
    server: true,
    default: (): FaqItem[] => [],
    transform: (response: FaqsApiResponse): FaqItem[] => response.data ?? [],
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
