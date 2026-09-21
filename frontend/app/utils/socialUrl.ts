const PLACEHOLDER_HOSTS = new Set([
  'facebook.com',
  'linkedin.com',
  'x.com',
  'twitter.com',
  'youtube.com',
  'instagram.com'
])

/** True when a settings URL is a real profile, not a generic network homepage. */
export const isPublicProfileUrl = (href: string | null | undefined): boolean => {
  if (!href || !/^https?:\/\//i.test(href)) {
    return false
  }

  try {
    const url = new URL(href)
    const host = url.hostname.replace(/^www\./i, '').toLowerCase()
    const path = url.pathname.replace(/\/+$/, '')

    if (PLACEHOLDER_HOSTS.has(host) && (path === '' || path === '/')) {
      return false
    }

    return true
  } catch {
    return false
  }
}
