/**
 * Server-only Laravel API helpers for Nuxt SSR.
 * Attaches X-SSR-Secret only on the server — never exposed to public runtimeConfig.
 */

export const SSR_SECRET_HEADER = 'X-SSR-Secret'

/** Headers for trusted Nuxt → Laravel SSR reads (empty on client / when unset). */
export const useLaravelApiHeaders = (): Record<string, string> => {
  if (!import.meta.server) {
    return {}
  }

  const secret = useRuntimeConfig().ssrInternalSecret
  if (typeof secret !== 'string' || secret.trim() === '') {
    return {}
  }

  return { [SSR_SECRET_HEADER]: secret.trim() }
}

type NuxtAppLike = ReturnType<typeof useNuxtApp>

/** Reuse payload during SSR so child setups after await do not refetch. */
export const laravelGetCachedData = <DataT>(key: string, nuxtApp: NuxtAppLike): DataT | undefined => {
  if (nuxtApp.payload.data[key] !== undefined) {
    return nuxtApp.payload.data[key] as DataT
  }
  if (nuxtApp.static?.data?.[key] !== undefined) {
    return nuxtApp.static.data[key] as DataT
  }
  return undefined
}

/**
 * Defaults for shared useAsyncData against Laravel.
 * - dedupe: 'defer' — do not abort in-flight shared keys
 * - getCachedData — reuse SSR payload for shared keys
 */
export const useLaravelFetchDefaults = <DataT = unknown>() => ({
  dedupe: 'defer' as const,
  getCachedData: (key: string, nuxtApp: NuxtAppLike) => laravelGetCachedData<DataT>(key, nuxtApp)
})

/** $fetch wrapper that adds SSR secret on server. */
export const laravelFetch = <T>(
  url: string,
  opts: Parameters<typeof $fetch<T>>[1] = {}
): Promise<T> => {
  const headers = {
    ...useLaravelApiHeaders(),
    ...(opts?.headers as Record<string, string> | undefined)
  }

  return $fetch<T>(url, {
    ...opts,
    headers
  }) as Promise<T>
}
