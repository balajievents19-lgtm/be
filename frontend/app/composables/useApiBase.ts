import { resolveApiBase } from '~/utils/apiBase'

export const useApiBase = () => {
  const config = useRuntimeConfig()

  return resolveApiBase({
    isServer: import.meta.server,
    isDev: import.meta.dev,
    publicBase: String(config.public.apiBase || ''),
    internalBase: String(config.apiInternalBase || '')
  })
}
