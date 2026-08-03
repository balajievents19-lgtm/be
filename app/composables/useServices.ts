import { computed, ref } from 'vue'
import type { HomePayload } from '~/types/home'
import type { Service, ServiceDetailResponse, ServiceListResponse } from '~/types/service'

const emptyList: Service[] = []

export const useServicesApi = () => {
  const base = useApiBase()

  const fetchServices = async (): Promise<Service[]> => {
    const response = await $fetch<ServiceListResponse>(`${base}/services`)
    return response.data ?? emptyList
  }

  const fetchServiceBySlug = async (slug: string): Promise<Service | null> => {
    if (!slug) {
      return null
    }

    const response = await $fetch<ServiceDetailResponse>(`${base}/services/${encodeURIComponent(slug)}`)
    return response.data ?? null
  }

  return {
    fetchServices,
    fetchServiceBySlug
  }
}

/** Shared cached list for layout + pages. Reuses /api/home on the homepage. */
export const useServices = async () => {
  const route = useRoute()
  const failed = useState('services-api-failed', () => false)
  const homePayload = useNuxtData<HomePayload>('home')

  // Homepage already loaded featured services via the aggregator — no second request.
  if (route.path === '/' && homePayload.data.value) {
    failed.value = false

    return {
      data: computed(() => homePayload.data.value?.featured_services ?? emptyList),
      pending: computed(() => false),
      error: ref(null),
      status: computed(() => 'success' as const),
      refresh: async () => {},
      clear: () => {},
      execute: async () => {},
      failed
    }
  }

  const { fetchServices } = useServicesApi()

  const asyncData = await useAsyncData('services', async () => {
    try {
      const items = await fetchServices()
      failed.value = false
      return items
    } catch {
      failed.value = true
      return emptyList
    }
  }, {
    default: () => emptyList,
    server: true
  })

  return {
    ...asyncData,
    failed
  }
}

export const useService = async (slug: MaybeRefOrGetter<string>) => {
  const { fetchServiceBySlug } = useServicesApi()
  const failed = useState('service-detail-api-failed', () => false)

  const asyncData = await useAsyncData(
    () => `service-${toValue(slug)}`,
    async () => {
      try {
        const item = await fetchServiceBySlug(toValue(slug))
        failed.value = false
        return item
      } catch {
        failed.value = true
        return null
      }
    },
    {
      default: () => null,
      watch: [() => toValue(slug)]
    }
  )

  return {
    ...asyncData,
    failed
  }
}
