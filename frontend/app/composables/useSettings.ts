import type { ComputedRef, Ref } from 'vue'
import type { HomePayload, SiteSettings } from '~/types/home'
import { emptySettings } from '~/types/home'

interface SettingsApiResponse {
  data: SiteSettings
}

const homeSettingsReady = (settings: SiteSettings | null | undefined): boolean => {
  if (!settings) {
    return false
  }

  return Boolean(
    settings.company?.name
    || settings.contact?.whatsapp
    || settings.contact?.phone
    || settings.contact?.email
  )
}

export interface UseSettingsResult {
  data: ComputedRef<SiteSettings>
  pending: Ref<boolean> | ComputedRef<boolean>
  error: Ref<Error | null>
  failed: Ref<boolean> | ComputedRef<boolean>
}

/**
 * Shared site settings. One SSR request via key + dedupe:defer.
 * Graceful fallback on upstream failure (no NuxtError payload).
 */
export const useSettings = (): UseSettingsResult => {
  const home = useNuxtData<HomePayload>('home')
  const base = useApiBase()
  const failed = useState('settings-api-failed', () => false)

  if (homeSettingsReady(home.data.value?.settings)) {
    return {
      data: computed(() => home.data.value!.settings),
      pending: computed(() => false),
      error: ref<Error | null>(null),
      failed: computed(() => false)
    }
  }

  const asyncData = useAsyncData<SiteSettings>(
    'settings',
    async () => {
      try {
        const response = await laravelFetch<SettingsApiResponse>(`${base}/settings`)
        failed.value = false
        return response.data
      } catch {
        failed.value = true
        return emptySettings()
      }
    },
    {
      ...useLaravelFetchDefaults<SiteSettings>(),
      server: true,
      default: (): SiteSettings => emptySettings()
    }
  )

  return {
    data: computed(() => {
      const fromHome = home.data.value?.settings
      if (homeSettingsReady(fromHome)) {
        return fromHome!
      }
      return asyncData.data.value ?? emptySettings()
    }),
    pending: asyncData.pending,
    error: asyncData.error as Ref<Error | null>,
    failed
  }
}
