export interface BreadcrumbItem {
  label: string
  to?: string
}

/** Content from reference/bootstrap-master/about.html */
export const aboutPageHeader = {
  title: 'About Us'
} as const

export const aboutBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'About Us' }
]

export { contactBoxes as aboutContactBoxes, contactInfoHeading as aboutContactHeading } from '~/data/contact'
