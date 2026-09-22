/**
 * Split Laravel API origin for Nuxt SSR vs the browser.
 *
 * Server (Nitro on the VPS) talks to Laravel on loopback.
 * The browser must use same-origin /api (Nginx → Laravel).
 * Never send 127.0.0.1:8000 to the user's browser.
 */

const stripSlash = (value: string): string => value.replace(/\/$/, '')

export const isLoopbackApiUrl = (value: string): boolean => {
  try {
    const url = new URL(value)
    return url.hostname === '127.0.0.1' || url.hostname === 'localhost' || url.hostname === '::1'
  } catch {
    return false
  }
}

export const resolveApiBase = (opts: {
  isServer: boolean
  isDev: boolean
  publicBase: string
  internalBase: string
}): string => {
  const publicBase = stripSlash(String(opts.publicBase || '').trim())
  const internalBase = stripSlash(String(opts.internalBase || '').trim())

  if (opts.isServer) {
    if (internalBase) {
      return internalBase
    }
    if (publicBase.startsWith('http://') || publicBase.startsWith('https://')) {
      return publicBase
    }
    return opts.isDev ? 'http://localhost:8000/api' : 'http://127.0.0.1:8000/api'
  }

  if (opts.isDev) {
    return publicBase || 'http://localhost:8000/api'
  }

  return '/api'
}

/** Laravel web origin (no /api) for Sanctum, sitemap, and robots. */
export const resolveLaravelWebOrigin = (apiBase: string): string => {
  const base = stripSlash(apiBase)
  if (!base || base === '/api' || base.startsWith('/')) {
    return ''
  }

  return base.replace(/\/api$/i, '')
}
