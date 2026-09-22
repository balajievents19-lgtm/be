/** Same-site Laravel storage path, including an optional query string. */
const STORAGE_PATH = /^\/storage\//

/**
 * Convert CMS media URLs to origin-relative /storage/... paths.
 * External URLs (YouTube, Instagram, CDNs) are left unchanged.
 */
export const toPublicMediaUrl = (value: string): string => {
  const trimmed = value.trim()
  if (!trimmed) {
    return value
  }

  if (STORAGE_PATH.test(trimmed) || trimmed.startsWith('/images/') || trimmed.startsWith('/_nuxt/')) {
    return trimmed
  }

  const absolute = trimmed.startsWith('//') ? `https:${trimmed}` : trimmed
  if (!/^https?:\/\//i.test(absolute)) {
    return trimmed
  }

  try {
    const url = new URL(absolute)
    if (url.pathname.startsWith('/storage/')) {
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
