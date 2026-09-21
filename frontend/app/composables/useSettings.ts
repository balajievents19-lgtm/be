import type { ComputedRef, Ref } from 'vue'
import type { HomePayload, SiteSettings } from '~/types/home'
import { emptySettings } from '~/types/home'
import { laravelGetCachedData, laravelFetch, useLaravelFetchDefaults } from '~/composables/useLaravelApi'

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
  const route = useRoute()
  const home = useNuxtData<HomePayload>('home')
  const base = useApiBase()
  const failed = useState('settings-api-failed', () => false)
  const onHomepage = route.path === '/'
  const homeReady = homeSettingsReady(home.data.value?.settings)

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
      // Homepage SSR already awaits /api/home, which includes settings.
      immediate: !onHomepage && !homeReady,
      default: (): SiteSettings => emptySettings(),
      getCachedData: (key, nuxtApp) => {
        const fromHome = (nuxtApp.payload.data.home as HomePayload | undefined)?.settings
        if (homeSettingsReady(fromHome)) {
          return fromHome
        }
        return laravelGetCachedData<SiteSettings>(key, nuxtApp)
      }
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
    pending: computed(() => {
      if (homeSettingsReady(home.data.value?.settings)) {
        return false
      }
      return Boolean(asyncData.pending.value)
    }),
    error: asyncData.error as Ref<Error | null>,
    failed
  }
}
