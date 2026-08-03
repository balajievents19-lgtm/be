import type { Service, ServiceDetailResponse, ServiceListResponse } from '~/types/service'

const emptyList: Service[] = []

export const useServicesApi = () => {
  const config = useRuntimeConfig()
  const base = String(config.public.apiBase || 'http://127.0.0.1:8000/api').replace(/\/$/, '')

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

/** Shared cached list for layout + pages. */
export const useServices = async () => {
  const { fetchServices } = useServicesApi()
  const failed = useState('services-api-failed', () => false)

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
