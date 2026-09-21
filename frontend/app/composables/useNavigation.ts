import type { HomePayload } from '~/types/home'
import type { CmsNavigationItem, NavLink } from '~/types/navigation'

interface NavigationApiResponse {
  data: CmsNavigationItem[]
}

interface NavigationBundle {
  header: CmsNavigationItem[]
  footer: CmsNavigationItem[]
}

const FALLBACK_HEADER: NavLink[] = [
  { label: 'Home', to: '/' },
  { label: 'About Us', to: '/about' },
  { label: 'Services', to: '/services', children: true },
  { label: 'Events', to: '/events' },
  { label: 'Gallery', to: '/gallery' },
  { label: 'Packages', to: '/packages' },
  { label: 'Blog', to: '/blog' },
  { label: 'FAQ’s', to: '/faq' },
  { label: 'Contact us', to: '/contact' }
]

const FALLBACK_FOOTER: NavLink[] = [
  { label: 'Home', to: '/' },
  { label: 'About', to: '/about' },
  { label: 'Services', to: '/services' },
  { label: 'Events', to: '/events' },
  { label: 'Gallery', to: '/gallery' },
  { label: 'Packages', to: '/packages' },
  { label: 'FAQ', to: '/faq' },
  { label: 'Contact', to: '/contact' },
  { label: 'Blog', to: '/blog' }
]

const normalizePath = (url: string): string => {
  if (!url || /^https?:\/\//i.test(url) || url.startsWith('mailto:') || url.startsWith('tel:')) {
    return url
  }

  const path = url.startsWith('/') ? url : `/${url}`
  if (path.length > 1 && path.endsWith('/')) {
    return path.slice(0, -1)
  }

  return path
}

const isExternalUrl = (url: string, target?: string | null): boolean => {
  if (target === '_blank') {
    return true
  }

  return /^https?:\/\//i.test(url) || url.startsWith('mailto:') || url.startsWith('tel:')
}

export const mapCmsNavigation = (
  items: CmsNavigationItem[] | null | undefined,
  placement: 'header' | 'footer'
): NavLink[] => {
  if (!items?.length) {
    return placement === 'header' ? [...FALLBACK_HEADER] : [...FALLBACK_FOOTER]
  }

  const topLevel = items
    .filter(item => item.parent_id == null)
    .slice()
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.id - b.id)

  if (!topLevel.length) {
    return placement === 'header' ? [...FALLBACK_HEADER] : [...FALLBACK_FOOTER]
  }

  return topLevel.map((item) => {
    const to = normalizePath(item.url)
    const servicesMega = placement === 'header' && to === '/services'

    return {
      id: item.id,
      label: item.label,
      to,
      target: item.target || '_self',
      external: isExternalUrl(item.url, item.target),
      children: servicesMega || undefined
    }
  })
}

const extractItems = (response: NavigationApiResponse | CmsNavigationItem[]): CmsNavigationItem[] => {
  if (Array.isArray(response)) {
    return response
  }

  return response?.data ?? []
}

/** Header/footer menus from CMS. Reuses /api/home nav when present. */
export const useNavigation = () => {
  const base = useApiBase()
  const home = useNuxtData<HomePayload>('home')
  const failed = useState('navigation-api-failed', () => false)

  const homePayload = home.data.value
  if (
    homePayload
    && Array.isArray(homePayload.header_navigation)
    && Array.isArray(homePayload.footer_navigation)
  ) {
    failed.value = false

    return {
      header: computed(() => mapCmsNavigation(home.data.value?.header_navigation, 'header')),
      footer: computed(() => mapCmsNavigation(home.data.value?.footer_navigation, 'footer')),
      pending: computed(() => false),
      failed: computed(() => false)
    }
  }

  const asyncData = useAsyncData(
    'navigation',
    async (): Promise<NavigationBundle> => {
      try {
        const [headerResponse, footerResponse] = await Promise.all([
          laravelFetch<NavigationApiResponse | CmsNavigationItem[]>(`${base}/navigation`, {
            query: { placement: 'header' }
          }),
          laravelFetch<NavigationApiResponse | CmsNavigationItem[]>(`${base}/navigation`, {
            query: { placement: 'footer' }
          })
        ])

        failed.value = false

        return {
          header: extractItems(headerResponse),
          footer: extractItems(footerResponse)
        }
      } catch {
        failed.value = true

        return {
          header: [],
          footer: []
        }
      }
    },
    {
      ...useLaravelFetchDefaults<NavigationBundle>(),
      server: true,
      default: (): NavigationBundle => ({
        header: [],
        footer: []
      })
    }
  )

  return {
    header: computed(() => mapCmsNavigation(asyncData.data.value?.header, 'header')),
    footer: computed(() => mapCmsNavigation(asyncData.data.value?.footer, 'footer')),
    pending: computed(() => Boolean(asyncData.pending.value)),
    failed
  }
}
