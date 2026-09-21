export const useApiBase = () => {
  const config = useRuntimeConfig()
  const configured = String(config.public.apiBase || '').replace(/\/$/, '')
  const fallback = import.meta.dev ? 'http://localhost:8000/api' : ''
  let base = configured || fallback

  if (import.meta.client) {
    try {
      const api = new URL(base, window.location.origin)
      const pageHost = window.location.hostname
      if (
        (api.hostname === '127.0.0.1' && pageHost === 'localhost')
        || (api.hostname === 'localhost' && pageHost === '127.0.0.1')
      ) {
        api.hostname = pageHost
        base = `${api.origin}${api.pathname}`.replace(/\/$/, '')
      }
    } catch {
      // Keep configured base if it is not a valid absolute URL.
    }
  }

  return base
}
