import { services } from '~/data/home'

export interface FaqItem {
  id: string
  question: string
  answer: string
}

export interface BreadcrumbItem {
  label: string
  to?: string
}

export const faqPageHeader = {
  title: 'FAQ’s'
} as const

export const faqBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'FAQ’s' }
]

const serviceNames = services.map(item => item.title).join(', ')

/**
 * FAQ copy is assembled only from master contact / services facts.
 * Layout matches master `.faq-list` / `.faq-slide` CSS (no faq.html in ZIP).
 */
export const faqItems: FaqItem[] = [
  {
    id: 'faq-1',
    question: 'How can I contact Balaji Events?',
    answer: 'You can call us at +91-9462577065, +91-8058780290. Email balajievents19@gmail.com.'
  },
  {
    id: 'faq-2',
    question: 'Where is your office located?',
    answer: 'Shop No.15, Road No.3, Opposite : Jamuna Resort Jhunjhunu (Rajasthan)'
  },
  {
    id: 'faq-3',
    question: 'What services do you provide?',
    answer: `Our services include ${serviceNames}.`
  },
  {
    id: 'faq-4',
    question: 'Do you manage weddings in Rajasthan?',
    answer: 'Balaji Events is trusted wedding management company in india. We were Provide many differnt service in Rajasthan. We promise to set the perfect pitch for to-be and groom to celebrate their special day in grand way.'
  },
  {
    id: 'faq-5',
    question: 'How do I send an inquiry?',
    answer: 'Use the Contact Form on the Contact us page with your name, email, subject, and message.'
  }
]

export const faqCta = {
  label: 'Contact Us',
  to: '/contact'
} as const
