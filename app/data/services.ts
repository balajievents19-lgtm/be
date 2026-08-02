import type { BreadcrumbItem } from '~/data/about'

/** Page chrome for /services (master services.html is not supplied; uses .service-type + page-header). */
export const servicesPageHeader = {
  title: 'Services'
} as const

export const servicesBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'Services' }
]
