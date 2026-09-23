/**
 * Safe gallery video embeds. Never iframe arbitrary URLs.
 */

const ALLOWED_EMBED_HOSTS = new Set([
  'www.youtube-nocookie.com',
  'www.youtube.com',
  'youtube.com',
  'player.vimeo.com',
  'www.instagram.com',
  'instagram.com',
  'www.facebook.com',
  'facebook.com'
])

export const isSafeGalleryEmbedUrl = (url: string | null | undefined): boolean => {
  if (!url) {
    return false
  }

  try {
    const parsed = new URL(url)
    if (parsed.protocol !== 'https:') {
      return false
    }

    const host = parsed.hostname.toLowerCase()
    if (!ALLOWED_EMBED_HOSTS.has(host)) {
      return false
    }

    if (host.includes('instagram.com')) {
      return /\/(reel|p|tv)\/[^/]+\/embed\/?$/i.test(parsed.pathname)
    }

    if (host.includes('facebook.com')) {
      return parsed.pathname.startsWith('/plugins/video.php')
    }

    return true
  } catch {
    return false
  }
}

export const isYouTubeNocookieEmbedUrl = (url: string | null | undefined): boolean => {
  if (!url) {
    return false
  }

  try {
    const parsed = new URL(url)
    if (parsed.protocol !== 'https:') {
      return false
    }

    return parsed.hostname.toLowerCase() === 'www.youtube-nocookie.com'
      && /^\/embed\/[A-Za-z0-9_-]{6,}$/.test(parsed.pathname)
  } catch {
    return false
  }
}

export const youtubeEmbedUrlFromId = (id: string | null | undefined): string | null => {
  if (!id || !/^[A-Za-z0-9_-]{6,}$/.test(id)) {
    return null
  }

  return `https://www.youtube-nocookie.com/embed/${id}`
}

const isYouTubeOutboundUrl = (url: string | null | undefined): boolean => {
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

export const isSafeGalleryOpenUrl = (url: string | null | undefined): boolean => {
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
