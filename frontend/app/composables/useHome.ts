import type { HomeApiResponse, HomePayload } from '~/types/home'
import { emptyHome } from '~/types/home'

/** Single homepage aggregator request. */
export const useHome = async () => {
  const base = useApiBase()
  const failed = useState('home-api-failed', () => false)

  const asyncData = await useAsyncData(
    'home',
    async () => {
      try {
        const response = await laravelFetch<HomeApiResponse>(`${base}/home`)
        failed.value = false
        return response.data
      } catch {
        failed.value = true
        return emptyHome()
      }
    },
    {
      ...useLaravelFetchDefaults<HomePayload>(),
      server: true,
      default: (): HomePayload => emptyHome()
    }
  )

  return {
    ...asyncData,
    failed
  }
}
