import { resolveApiBase, resolveLaravelWebOrigin } from '~/utils/apiBase'

/** Laravel web origin (sitemap/robots), not the /api prefix. */
export const laravelWebOrigin = (): string => {
  const config = useRuntimeConfig()
  const apiBase = resolveApiBase({
    isServer: true,
    isDev: import.meta.dev,
    publicBase: String(config.public.apiBase || ''),
    internalBase: String(config.apiInternalBase || '')
  })

  return resolveLaravelWebOrigin(apiBase)
}
