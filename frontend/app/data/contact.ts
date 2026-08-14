export interface BreadcrumbItem {
  label: string
  to?: string
}

export interface ContactBox {
  icon: string
  type: 'phones' | 'address' | 'email' | 'text'
  phones?: { label: string, href: string }[]
  address?: string
  lines?: { label: string, href?: string, text: string }[]
  text?: string
}

/** Shared contact boxes from master about.html / contact.html */
export const contactBoxes: ContactBox[] = [
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

export const contactInfoHeading = {
  title: 'Contact Us',
  info: 'All the information you will need is listed below, just click on the page you want to view and that\'s it.'
} as const

export const contactPageHeader = {
  title: 'contact us'
} as const

export const contactBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'Contact us' }
]
