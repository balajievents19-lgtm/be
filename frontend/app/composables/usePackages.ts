import type { ServicePackage } from '~/types/package'

export interface ServicePackageListResponse {
  data: ServicePackage[]
}

const emptyList: ServicePackage[] = []

/** Packages list from GET /api/service-packages. */
export const usePackages = async () => {
  const base = useApiBase()
  const failed = useState('packages-api-failed', () => false)

  const asyncData = await useAsyncData(
    'service-packages',
    async () => {
      try {
        const response = await laravelFetch<ServicePackageListResponse>(`${base}/service-packages`)
        failed.value = false
        return response.data ?? emptyList
      } catch {
        failed.value = true
        return emptyList
      }
    },
    {
      ...useLaravelFetchDefaults<ServicePackage[]>(),
      server: true,
      default: (): ServicePackage[] => emptyList
    }
  )

  return {
    ...asyncData,
    failed
  }
}
