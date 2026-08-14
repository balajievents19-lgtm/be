import { sanitizeCmsHtml, sanitizeMapEmbed } from '~/utils/sanitizeHtml'

export type SanitizeHtmlProfile = 'cms' | 'map'

/**
 * SSR-safe sanitized HTML for public CMS / map embed rendering.
 * Prefer SharedSafeHtml for templates so v-html stays in one place.
 */
export function useSanitizedHtml(
  source: MaybeRefOrGetter<string | null | undefined>,
  profile: SanitizeHtmlProfile = 'cms'
) {
  return computed(() => {
    const value = toValue(source)
    return profile === 'map'
      ? sanitizeMapEmbed(value)
      : sanitizeCmsHtml(value)
  })
}
