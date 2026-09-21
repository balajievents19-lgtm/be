import type { HomePayload } from '~/types/home'
import type { ExternalMediaItem } from '~/utils/externalMedia'

const emptyList: ExternalMediaItem[] = []

export const useExternalMedia = (options?: { homepage?: boolean, server?: boolean }) => {
  const homepage = options?.homepage === true
  const key = homepage ? 'external-media-homepage' : 'external-media'
  const failed = useState(`${key}-failed`, () => false)
  const homePayload = useNuxtData<HomePayload>('home')
  const fromHome = homepage ? homePayload.data.value?.external_media : undefined

  if (homepage && Array.isArray(fromHome)) {
    failed.value = false

    return {
      data: computed(() => homePayload.data.value?.external_media ?? emptyList),
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
  const path = homepage ? '/external-media?homepage=1' : '/external-media'

  const asyncData = useAsyncData(
    key,
    async () => {
      try {
        const response = await laravelFetch<{ data: ExternalMediaItem[] }>(`${base}${path}`)
        failed.value = false
        return response.data ?? emptyList
      } catch {
        failed.value = true
        return emptyList
      }
    },
    {
      ...useLaravelFetchDefaults<ExternalMediaItem[]>(),
      server: options?.server !== false,
      default: (): ExternalMediaItem[] => emptyList
    }
  )

  return Object.assign(asyncData, {
    failed
  }) as typeof asyncData & { failed: typeof failed }
}
