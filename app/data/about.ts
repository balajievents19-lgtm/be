export interface BreadcrumbItem {
  label: string
  to?: string
}

export interface AboutContactBox {
  icon: string
  type: 'phones' | 'address' | 'email'
  phones?: { label: string, href: string }[]
  address?: string
  lines?: { label: string, href?: string, text: string }[]
}

/** Content from reference/bootstrap-master/about.html */
export const aboutPageHeader = {
  title: 'About Us'
} as const

export const aboutBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'About Us' }
]

export const aboutContactHeading = {
  title: 'Contact Us',
  info: 'All the information you will need is listed below, just click on the page you want to view and that\'s it.'
} as const

export const aboutContactBoxes: AboutContactBox[] = [
  {
    icon: 'icon-phone',
    type: 'phones',
    phones: [
      { label: '+91-9462577065', href: 'tel:+919462577065' },
      { label: '+91-8058780290', href: 'tel:+918058780290' }
    ]
  },
  {
    icon: 'icon-location-1',
    type: 'address',
    address: 'Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)'
  },
  {
    icon: 'icon-message',
    type: 'email',
    lines: [
      {
        label: 'Email - ',
        text: 'balajievents19@gmail.com',
        href: 'mailto:balajievents19@gmail.com'
      },
      {
        label: 'Website - ',
        text: 'Balajievent.net',
        href: '/'
      }
    ]
  }
]
