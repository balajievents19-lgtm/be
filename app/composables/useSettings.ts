import type { HomePayload, SiteSettings } from '~/types/home'
import { emptySettings } from '~/types/home'

interface SettingsApiResponse {
  data: SiteSettings
}

/** Reuses homepage cache when present; otherwise GET /settings. */
export const useSettings = async () => {
  const home = useNuxtData<HomePayload>('home')
  if (home.data.value?.settings) {
    return {
      data: computed(() => home.data.value!.settings),
      pending: computed(() => false),
      error: ref(null),
      failed: computed(() => false)
    }
  }

  const base = useApiBase()
  const failed = useState('settings-api-failed', () => false)

  const asyncData = await useFetch(`${base}/settings`, {
    key: 'settings',
    server: true,
    default: (): SiteSettings => emptySettings(),
    transform: (response: SettingsApiResponse): SiteSettings => response.data,
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
