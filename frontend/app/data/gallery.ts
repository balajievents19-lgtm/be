export interface BreadcrumbItem {
  label: string
  to?: string
}

export const galleryPageHeader = {
  title: 'Gallery'
} as const

export const galleryBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'Gallery' }
]
