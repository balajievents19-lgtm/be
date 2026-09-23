/**
 * Safe external media URL helpers (frontend mirror of backend ExternalMediaUrl).
 * Never render arbitrary iframes.
 */

export type ExternalMediaMode = 'embed' | 'link'

export interface ExternalMediaItem {
  id: number
  title: string
  media_type: string
  provider: string
  url: string | null
  embed_url: string | null
  mode: ExternalMediaMode
  cta_label: string | null
  thumbnail: string | null
  description: string | null
  sort_order?: number
  homepage_featured?: boolean
}

const ALLOWED_EMBED_HOSTS = new Set([
  'www.youtube-nocookie.com',
  'www.youtube.com',
  'youtube.com',
  'player.vimeo.com'
])

export const isSafeEmbedUrl = (url: string | null | undefined): boolean => {
  if (!url) {
    return false
  }
  try {
    const parsed = new URL(url)
    if (parsed.protocol !== 'https:' && parsed.protocol !== 'http:') {
      return false
    }
    return ALLOWED_EMBED_HOSTS.has(parsed.hostname.toLowerCase())
  } catch {
    return false
  }
}

export const isYouTubeOutboundUrl = (url: string | null | undefined): boolean => {
  if (!url) {
    return false
  }

  try {
    const parsed = new URL(url)
    const host = parsed.hostname.toLowerCase()

    if (host === 'youtu.be' || host === 'www.youtu.be' || host === 'm.youtu.be') {
      return true
    }

    if (host === 'youtube.com' || host === 'www.youtube.com' || host === 'm.youtube.com') {
      return !parsed.pathname.toLowerCase().startsWith('/embed/')
    }

    return false
  } catch {
    return false
  }
}

export const isSafeOpenUrl = (url: string | null | undefined): boolean => {
  if (!url || isYouTubeOutboundUrl(url)) {
    return false
  }
  try {
    const parsed = new URL(url)
    return parsed.protocol === 'https:' || parsed.protocol === 'http:'
  } catch {
    return false
  }
}
