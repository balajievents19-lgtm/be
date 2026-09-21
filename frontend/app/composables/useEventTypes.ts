import type { EventType, EventTypeListResponse } from '~/types/eventType'
import type { HomePayload } from '~/types/home'

const emptyList: EventType[] = []

export const useEventTypes = (options?: { server?: boolean }) => {
  const homePayload = useNuxtData<HomePayload>('home')
  const fromHome = homePayload.data.value?.event_types
  const failed = useState('event-types-api-failed', () => false)

  if (Array.isArray(fromHome)) {
    failed.value = false

    return {
      data: computed(() => homePayload.data.value?.event_types ?? emptyList),
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
  const server = options?.server ?? true

  const asyncData = useAsyncData(
    'event-types',
    async () => {
      try {
        const response = await laravelFetch<EventTypeListResponse>(`${base}/event-types`)
        failed.value = false
        return response.data ?? emptyList
      } catch {
        failed.value = true
        return emptyList
      }
    },
    {
      ...useLaravelFetchDefaults<EventType[]>(),
      default: (): EventType[] => emptyList,
      server
    }
  )

  return Object.assign(asyncData, { failed })
}
