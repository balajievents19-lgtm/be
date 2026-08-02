import type { Service, ServiceDetailResponse, ServiceListResponse } from '~/types/service'

const emptyList: Service[] = []

export const useServicesApi = () => {
  const config = useRuntimeConfig()
  const base = String(config.public.apiBase || 'http://127.0.0.1:8000/api').replace(/\/$/, '')

  const fetchServices = async (): Promise<Service[]> => {
    try {
      const response = await $fetch<ServiceListResponse>(`${base}/services`)
      return response.data ?? emptyList
    } catch {
      return emptyList
    }
  }

  const fetchServiceBySlug = async (slug: string): Promise<Service | null> => {
    if (!slug) {
      return null
    }

    try {
      const response = await $fetch<ServiceDetailResponse>(`${base}/services/${encodeURIComponent(slug)}`)
      return response.data ?? null
    } catch {
      return null
    }
  }

  return {
    fetchServices,
    fetchServiceBySlug
  }
}

/** Shared cached list for layout + pages. */
export const useServices = async () => {
  const { fetchServices } = useServicesApi()

  return useAsyncData('services', fetchServices, {
    default: () => emptyList,
    server: true
  })
}

export const useService = async (slug: MaybeRefOrGetter<string>) => {
  const { fetchServiceBySlug } = useServicesApi()

  return useAsyncData(
    () => `service-${toValue(slug)}`,
    () => fetchServiceBySlug(toValue(slug)),
    {
      default: () => null,
      watch: [() => toValue(slug)]
    }
  )
}

export const serviceIconClass = (service: Pick<Service, 'icon'>): string => {
  const icon = service.icon?.trim()
  if (!icon) {
    return 'icon-calander'
  }
  if (icon.startsWith('icon-')) {
    return icon
  }
  return `icon-${icon}`
}
