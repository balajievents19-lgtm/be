export const useApiBase = () => {
  const config = useRuntimeConfig()

  return String(config.public.apiBase || 'http://127.0.0.1:8000/api').replace(/\/$/, '')
}
