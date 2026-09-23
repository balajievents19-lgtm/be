/** Same-site Laravel storage path, including an optional query string. */
const STORAGE_PATH = /^\/storage\//
const PROTECTED_MEDIA_PATH = /^\/protected-media\//

/**
 * Convert CMS media URLs to origin-relative paths.
 * Protected display URLs and leftover /storage paths stay same-origin.
 * External URLs (YouTube, Instagram, CDNs) are left unchanged.
 */
export const toPublicMediaUrl = (value: string): string => {
  const trimmed = value.trim()
  if (!trimmed) {
    return value
  }

  if (
    STORAGE_PATH.test(trimmed)
    || PROTECTED_MEDIA_PATH.test(trimmed)
    || trimmed.startsWith('/images/')
    || trimmed.startsWith('/_nuxt/')
  ) {
    return trimmed
  }

  const absolute = trimmed.startsWith('//') ? `https:${trimmed}` : trimmed
  if (!/^https?:\/\//i.test(absolute)) {
    return trimmed
  }

  try {
    const url = new URL(absolute)
    if (url.pathname.startsWith('/storage/') || url.pathname.startsWith('/protected-media/')) {
      return `${url.pathname}${url.search}`
    }
  } catch {
    return trimmed
  }

  return trimmed
}

/** Walk JSON from Laravel and rewrite only same-site storage image URLs. */
export const rewritePublicStorageUrls = <T>(value: T): T => {
  if (typeof value === 'string') {
    return toPublicMediaUrl(value) as T
  }

  // Binary downloads (gallery original) must not be walked as JSON objects.
  if (typeof Blob !== 'undefined' && value instanceof Blob) {
    return value
  }
  if (typeof ArrayBuffer !== 'undefined' && value instanceof ArrayBuffer) {
    return value
  }

  if (Array.isArray(value)) {
    return value.map(item => rewritePublicStorageUrls(item)) as T
  }

  if (value && typeof value === 'object') {
    const out: Record<string, unknown> = {}
    for (const [key, nested] of Object.entries(value as Record<string, unknown>)) {
      out[key] = rewritePublicStorageUrls(nested)
    }

    return out as T
  }

  return value
}

/**
 * Open Graph / Twitter / JSON-LD image URLs must be absolute.
 * Page <img> tags stay origin-relative via toPublicMediaUrl.
 */
export const toAbsoluteSeoMediaUrl = (value: string, origin: string): string => {
  const trimmed = value.trim()
  if (!trimmed) {
    return value
  }

  const base = origin.replace(/\/$/, '')
  if (
    trimmed.startsWith('/storage/')
    || trimmed.startsWith('/protected-media/')
    || trimmed.startsWith('/images/')
  ) {
    return `${base}${trimmed}`
  }

  return trimmed
}

export const absolutizeSeoMediaUrls = <T>(value: T, origin: string): T => {
  if (typeof value === 'string') {
    return toAbsoluteSeoMediaUrl(value, origin) as T
  }

  if (Array.isArray(value)) {
    return value.map(item => absolutizeSeoMediaUrls(item, origin)) as T
  }

  if (value && typeof value === 'object') {
    const out: Record<string, unknown> = {}
    for (const [key, nested] of Object.entries(value as Record<string, unknown>)) {
      out[key] = absolutizeSeoMediaUrls(nested, origin)
    }

    return out as T
  }

  return value
}
