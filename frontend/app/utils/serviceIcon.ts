/**
 * Map CMS/Filament icon tokens + service slugs onto the project icon font
 * (untitled-font-20 / icons.css). Never invent services — only presentation.
 */

const SLUG_ICON: Record<string, string> = {
  'wedding-planning': 'icon-calander',
  'wedding': 'icon-heart',
  'tent-house': 'icon-banquet',
  'stage-decoration': 'icon-flower-pot',
  'catering': 'icon-caterers',
  'dj-sound': 'icon-music',
  'photography': 'icon-camera',
  'mehndi': 'icon-mehandi',
  'flower-decoration': 'icon-flower',
  'make-up': 'icon-beauty',
  'makeup': 'icon-beauty',
  'bridal-makeup': 'icon-beauty',
  'entertainment': 'icon-music',
  'gifts': 'icon-gift',
  'wedding-cards': 'icon-wedding-card',
  'corporate': 'icon-meeting',
  'corporate-events': 'icon-meeting',
  'event-management': 'icon-meeting',
  'birthday': 'icon-cake',
  'sangeet': 'icon-audio-visual'
}

/** CMS free-text / Heroicon-like tokens → project icon-* classes */
const TOKEN_ICON: Record<string, string> = {
  'calendar': 'icon-calander',
  'calander': 'icon-calander',
  'icon-calander': 'icon-calander',
  'tent': 'icon-banquet',
  'banquet': 'icon-banquet',
  'icon-banquet': 'icon-banquet',
  'sparkles': 'icon-flower-pot',
  // Filament often uses singular "sparkle" for bridal/makeup services
  'sparkle': 'icon-beauty',
  'utensils': 'icon-caterers',
  'catering': 'icon-caterers',
  'caterers': 'icon-caterers',
  'icon-caterers': 'icon-caterers',
  'music': 'icon-music',
  'icon-music': 'icon-music',
  'camera': 'icon-camera',
  'icon-camera': 'icon-camera',
  'flower': 'icon-flower',
  'flower-2': 'icon-flower-pot',
  'icon-flower': 'icon-flower',
  'icon-flower-pot': 'icon-flower-pot',
  'heart': 'icon-heart',
  'icon-heart': 'icon-heart',
  'cake': 'icon-cake',
  'icon-cake': 'icon-cake',
  'gift': 'icon-gift',
  'icon-gift': 'icon-gift',
  'meeting': 'icon-meeting',
  'briefcase': 'icon-meeting',
  'icon-meeting': 'icon-meeting',
  'mehandi': 'icon-mehandi',
  'mehndi': 'icon-mehandi',
  'icon-mehandi': 'icon-mehandi',
  'beauty': 'icon-beauty',
  'icon-beauty': 'icon-beauty',
  'grid': 'icon-grid-view',
  'grid-view': 'icon-grid-view',
  'icon-grid-view': 'icon-grid-view',
  'spoon': 'icon-spoon',
  'icon-spoon': 'icon-spoon',
  'wedding': 'icon-heart',
  'wedding-card': 'icon-wedding-card',
  'icon-wedding-card': 'icon-wedding-card'
}

const FALLBACK = 'icon-grid-view'

const normalize = (value: string) =>
  value.trim().toLowerCase().replace(/^icon-/, 'icon-')

/**
 * Resolve a safe project icon class for a service card.
 */
export const resolveServiceIcon = (
  icon: string | null | undefined,
  slug?: string | null
): string => {
  // Prefer slug maps first — CMS Heroicon tokens are often reused across services.
  if (slug) {
    const bySlug = SLUG_ICON[slug.toLowerCase()]
    if (bySlug) {
      return bySlug
    }
  }

  if (icon) {
    const raw = icon.trim()
    const keyed = raw.toLowerCase()
    const knownPrefixed = TOKEN_ICON[keyed]
    if (raw.startsWith('icon-') && knownPrefixed) {
      return knownPrefixed
    }
    if (raw.startsWith('icon-')) {
      // Already a project icon class — trust CMS when prefixed.
      return raw
    }
    const mapped = TOKEN_ICON[normalize(raw)] || TOKEN_ICON[keyed]
    if (mapped) {
      return mapped
    }
  }

  return FALLBACK
}
