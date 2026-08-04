import type { HomeApiResponse, HomePayload } from '~/types/home'
import { emptyHome } from '~/types/home'

/** Single homepage aggregator request. */
export const useHome = async () => {
  const base = useApiBase()
  const failed = useState('home-api-failed', () => false)

  const asyncData = await useFetch(`${base}/home`, {
    key: 'home',
    server: true,
    default: (): HomePayload => emptyHome(),
    transform: (response: HomeApiResponse): HomePayload => response.data,
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
