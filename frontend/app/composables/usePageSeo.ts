import { absolutizeSeoMediaUrls, toAbsoluteSeoMediaUrl } from '~/utils/publicMediaUrl'

export interface SeoMetaPayload {
  title?: string | null
  description?: string | null
  keywords?: string | null
  canonical?: string | null
  robots?: string | null
  image?: string | null
  open_graph?: {
    title?: string | null
    description?: string | null
    url?: string | null
    type?: string | null
    site_name?: string | null
    image?: string | null
    locale?: string | null
  } | null
  twitter?: {
    card?: string | null
    title?: string | null
    description?: string | null
    image?: string | null
  } | null
}

export interface SeoResolvePayload {
  meta: SeoMetaPayload
  schema: Array<Record<string, unknown>>
}

interface SeoResolveApiResponse {
  data: SeoResolvePayload
}

export interface UsePageSeoOptions {
  /** SeoController resolve type (home, about, service, gallery-category, …). */
  type: string
  slug?: string | null
  path?: string
  title?: string
  description?: string
  /**
   * When true, skip injecting JSON-LD scripts from the resolve payload.
   * Use when the page already emits the same schema elsewhere.
   */
  skipSchema?: boolean
  /** Drop schema blocks whose @type matches (e.g. avoid duplicate FAQPage). */
  omitSchemaTypes?: string[]
}

const emptyPayload = (): SeoResolvePayload => ({
  meta: {},
  schema: []
})

/**
 * Applies production SEO from GET /api/seo/resolve.
 * Shared key + dedupe:defer; graceful empty meta on upstream failure.
 *
 * Call this (without awaiting yet) before other setup awaits so Nuxt 4
 * still has instance context for useSeoMeta/useHead/useAsyncData.
 * Then `await` the returned promise so SSR HTML includes resolved meta.
 */
export const usePageSeo = async (options: UsePageSeoOptions) => {
  const base = useApiBase()
  const slug = options.slug ? String(options.slug) : ''
  const cacheKey = `seo-resolve:${options.type}:${slug}:${options.path || ''}:${options.title || ''}`
  const failed = useState(`seo-resolve-failed-${cacheKey}`, () => false)
  const payload = useState<SeoResolvePayload>(`${cacheKey}:payload`, () => emptyPayload())
  const requestOrigin = useRequestURL().origin

  const seoOrigin = computed(() => {
    const canonical = payload.value.meta?.canonical
    if (typeof canonical === 'string' && /^https?:\/\//i.test(canonical)) {
      try {
        return new URL(canonical).origin
      } catch {
        return requestOrigin
      }
    }

    return requestOrigin
  })

  const meta = computed(() => absolutizeSeoMediaUrls(payload.value.meta ?? {}, seoOrigin.value))
  const schemas = computed(() => {
    const list = payload.value.schema ?? []
    const omit = new Set(options.omitSchemaTypes ?? [])
    const filtered = omit.size
      ? list.filter((schema) => {
          const type = schema['@type']
          return typeof type !== 'string' || !omit.has(type)
        })
      : list

    return absolutizeSeoMediaUrls(filtered, seoOrigin.value)
  })

  // Register BEFORE fetch — Nuxt 4 loses instance context after async resume.
  useSeoMeta({
    title: () => meta.value.title || 'Balaji Royal Events',
    description: () => meta.value.description || undefined,
    robots: () => meta.value.robots || undefined,
    ogTitle: () => meta.value.open_graph?.title || meta.value.title || undefined,
    ogDescription: () => meta.value.open_graph?.description || meta.value.description || undefined,
    ogUrl: () => meta.value.open_graph?.url || meta.value.canonical || undefined,
    ogType: () => {
      const type = meta.value.open_graph?.type
      return type === 'article' ? 'article' : 'website'
    },
    ogImage: () => {
      const image = meta.value.open_graph?.image || meta.value.image || undefined
      return image ? toAbsoluteSeoMediaUrl(image, seoOrigin.value) : undefined
    },
    ogSiteName: () => meta.value.open_graph?.site_name || undefined,
    twitterCard: () => {
      const card = meta.value.twitter?.card
      return card === 'summary' || card === 'summary_large_image' || card === 'app' || card === 'player'
        ? card
        : 'summary_large_image'
    },
    twitterTitle: () => meta.value.twitter?.title || meta.value.title || undefined,
    twitterDescription: () => meta.value.twitter?.description || meta.value.description || undefined,
    twitterImage: () => {
      const image = meta.value.twitter?.image || meta.value.image || undefined
      return image ? toAbsoluteSeoMediaUrl(image, seoOrigin.value) : undefined
    }
  })

  useHead(() => {
    const canonical = meta.value.canonical
    const keywords = meta.value.keywords
    const script = options.skipSchema
      ? []
      : schemas.value.map((schema, index) => ({
          key: `ld-json-${options.type}-${index}`,
          type: 'application/ld+json' as const,
          innerHTML: JSON.stringify(schema)
        }))

    return {
      link: canonical
        ? [{ rel: 'canonical' as const, href: canonical, key: `canonical-${options.type}` }]
        : [],
      meta: keywords
        ? [{ name: 'keywords', content: keywords }]
        : [],
      script
    }
  })

  const query: Record<string, string> = {
    type: options.type
  }
  if (slug) {
    query.slug = slug
  }
  if (options.path) {
    query.path = options.path
  }
  if (options.title) {
    query.title = options.title
  }
  if (options.description) {
    query.description = options.description
  }

  const asyncData = await useAsyncData(
    cacheKey,
    async () => {
      try {
        const response = await laravelFetch<SeoResolveApiResponse>(`${base}/seo/resolve`, { query })
        failed.value = false
        return response.data ?? emptyPayload()
      } catch {
        failed.value = true
        return emptyPayload()
      }
    },
    {
      ...useLaravelFetchDefaults<SeoResolvePayload>(),
      server: true,
      default: (): SeoResolvePayload => emptyPayload()
    }
  )

  if (asyncData.data.value) {
    payload.value = asyncData.data.value
  }

  return Object.assign(asyncData, {
    meta,
    schemas,
    failed
  })
}
