import sanitizeHtml from 'sanitize-html'

/** Allowed hosts for Google Maps (and similar) iframe embeds. */
const MAP_EMBED_HOST_SUFFIXES = [
  'google.com',
  'google.co.in',
  'googleapis.com',
  'gstatic.com',
  'maps.google.com',
  'www.google.com',
  'maps.googleapis.com'
] as const

const CMS_ALLOWED_TAGS = [
  'p', 'br', 'hr',
  'strong', 'em', 'b', 'i', 'u', 's', 'sub', 'sup',
  'ul', 'ol', 'li',
  'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
  'a', 'blockquote', 'pre', 'code', 'span', 'div',
  'table', 'thead', 'tbody', 'tr', 'th', 'td',
  'img', 'figure', 'figcaption'
] as const

const CMS_ALLOWED_ATTRIBUTES: Record<string, string[]> = {
  'a': ['href', 'name', 'target', 'rel', 'title'],
  'img': ['src', 'alt', 'title', 'width', 'height', 'loading', 'decoding'],
  'td': ['colspan', 'rowspan'],
  'th': ['colspan', 'rowspan'],
  '*': ['class']
}

/**
 * Sanitize Admin RichEditor / CMS HTML for public SSR rendering.
 * Strips scripts, event handlers, and dangerous URL schemes.
 */
export function sanitizeCmsHtml(dirty: string | null | undefined): string {
  const input = (dirty ?? '').trim()
  if (!input) {
    return ''
  }

  return sanitizeHtml(input, {
    allowedTags: [...CMS_ALLOWED_TAGS],
    allowedAttributes: CMS_ALLOWED_ATTRIBUTES,
    allowedSchemes: ['http', 'https', 'mailto', 'tel'],
    allowedSchemesByTag: {
      img: ['http', 'https'],
      a: ['http', 'https', 'mailto', 'tel']
    },
    allowProtocolRelative: false,
    // Disallow style= to avoid CSS-based XSS / injection
    allowedStyles: {},
    transformTags: {
      a: (tagName, attribs) => {
        const href = attribs.href ?? ''
        if (!isSafeHref(href)) {
          const { href: _removed, ...rest } = attribs
          return { tagName, attribs: rest }
        }

        const next = { ...attribs }
        if (next.target === '_blank') {
          next.rel = mergeRel(next.rel, 'noopener noreferrer')
        }
        return { tagName, attribs: next }
      }
    }
  })
}

/**
 * Sanitize Contact map embed: HTTPS map URL or a single safe iframe.
 * Does not treat arbitrary rich HTML as valid map content.
 */
export function sanitizeMapEmbed(embed: string | null | undefined): string {
  const raw = (embed ?? '').trim()
  if (!raw) {
    return ''
  }

  if (/^https?:\/\//i.test(raw) && !/<[\s\S]*>/.test(raw)) {
    if (!isAllowedMapUrl(raw)) {
      return ''
    }
    return buildMapIframe(raw)
  }

  const cleaned = sanitizeHtml(raw, {
    allowedTags: ['iframe'],
    allowedAttributes: {
      iframe: [
        'src',
        'width',
        'height',
        'style',
        'allowfullscreen',
        'loading',
        'referrerpolicy',
        'frameborder',
        'allow',
        'title'
      ]
    },
    allowedSchemes: ['https', 'http'],
    allowProtocolRelative: false,
    exclusiveFilter(frame) {
      if (frame.tag !== 'iframe') {
        return true
      }
      const src = frame.attribs?.src
      return !src || !isAllowedMapUrl(src)
    }
  }).trim()

  if (!cleaned || !/^<iframe\b/i.test(cleaned)) {
    return ''
  }

  // Rebuild from the first iframe src so leftover markup cannot slip through.
  const srcMatch = cleaned.match(/\bsrc\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+))/i)
  const src = srcMatch?.[1] || srcMatch?.[2] || srcMatch?.[3] || ''
  if (!isAllowedMapUrl(src)) {
    return ''
  }

  return buildMapIframe(src)
}

export function isSafeHref(href: string): boolean {
  const value = href.trim()
  if (!value) {
    return false
  }
  if (value.startsWith('#') || value.startsWith('/')) {
    return true
  }
  return /^(https?:|mailto:|tel:)/i.test(value)
}

function isAllowedMapUrl(url: string): boolean {
  try {
    const parsed = new URL(url)
    if (parsed.protocol !== 'https:' && parsed.protocol !== 'http:') {
      return false
    }
    const host = parsed.hostname.toLowerCase()
    return MAP_EMBED_HOST_SUFFIXES.some(suffix =>
      host === suffix || host.endsWith(`.${suffix}`)
    )
  } catch {
    return false
  }
}

function buildMapIframe(src: string): string {
  const escaped = src
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

  return `<iframe src="${escaped}" width="600" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Office location map"></iframe>`
}

function mergeRel(existing: string | undefined, required: string): string {
  const parts = new Set(
    `${existing ?? ''} ${required}`
      .split(/\s+/)
      .map(part => part.trim())
      .filter(Boolean)
  )
  return [...parts].join(' ')
}
