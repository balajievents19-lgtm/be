import type { EventOverviewItem } from '~/types/home'

interface EventOverviewListResponse {
  data: EventOverviewItem[]
}

const emptyList: EventOverviewItem[] = []

export const useEventOverviews = (options?: { server?: boolean }) => {
  const base = useApiBase()
  const failed = useState('event-overviews-api-failed', () => false)
  const server = options?.server ?? true

  const asyncData = useAsyncData(
    'event-overviews',
    async () => {
      try {
        const response = await laravelFetch<EventOverviewListResponse>(`${base}/event-overviews`)
        failed.value = false
        return response.data ?? emptyList
      } catch {
        failed.value = true
        return emptyList
      }
    },
    {
      ...useLaravelFetchDefaults<EventOverviewItem[]>(),
      default: (): EventOverviewItem[] => emptyList,
      server
    }
  )

  return Object.assign(asyncData, { failed })
}
