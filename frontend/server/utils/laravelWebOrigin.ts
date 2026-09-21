/** Laravel web origin (sitemap/robots), not the /api prefix. */
export const laravelWebOrigin = (): string => {
  const config = useRuntimeConfig()
  const configured = String(config.public.apiBase || '').replace(/\/$/, '')
  const fallback = import.meta.dev ? 'http://localhost:8000/api' : ''
  const apiBase = configured || fallback
  if (!apiBase) {
    return ''
  }

  return apiBase.replace(/\/api$/i, '')
}
