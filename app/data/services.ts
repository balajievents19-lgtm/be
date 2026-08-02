import type { BreadcrumbItem } from '~/data/about'
import { services as serviceCatalog } from '~/data/home'

export interface ServiceDetail {
  slug: string
  title: string
  icon: string
  description: string
  features: string[]
  image: string
  gallery: string[]
}

const slugify = (title: string) =>
  title
    .toLowerCase()
    .replace(/&/g, 'and')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')

/** Descriptions reuse master homepage / about copy only — no invented marketing. */
const defaultDescription = 'Balaji Events is trusted wedding management company in india. We were Provide many differnt service in Rajasthan. We promise to set the perfect pitch for to-be and groom to celebrate their special day in grand way.'

const descriptionByTitle: Record<string, string> = {
  'Event Planner': 'Balaji Events is trusted wedding management company in india.We were Provide many differnt service in Rajasthan. We promise to set the perfect pitch for to-be and groom to celebrate their special day in grand way.',
  'Corporate Events': 'The corporate events includes planning and organizing different types of nts can be for a variety of reasons. Some of these include – conferences, team dinners, orientation for new joinees, farewell parties, trade shows, exhibitions, awards and incentive programs etc.',
  'Birthday Party': 'In This birthday party balji events provide Special Movement, Ballon Decsheets, Dinner, Special Cake, mmusical instruments, and much more variety.'
}

const serviceImages = [
  '/images/service-img/service-img1.png',
  '/images/service-img/service-img2.png',
  '/images/service-img/service-img3.png',
  '/images/service-img/service-img4.png',
  '/images/service-img/service-img5.png',
  '/images/service-img/service-img6.png',
  '/images/service-img/service-img7.png',
  '/images/service-img/service-img8.png',
  '/images/service-img/service-img9.png',
  '/images/service-img/service-img6.jpg'
]

const serviceGallery = [
  '/images/gallery/service-gallerImg1.jpg',
  '/images/gallery/service-gallerImg2.jpg',
  '/images/gallery/service-gallerImg3.jpg'
]

export const servicesPageHeader = {
  title: 'Services'
} as const

export const servicesBreadcrumbs: BreadcrumbItem[] = [
  { label: 'Home', to: '/' },
  { label: 'Services' }
]

export const serviceDetails: ServiceDetail[] = serviceCatalog.map((item, index) => {
  const slug = slugify(item.title)
  const peerFeatures = serviceCatalog
    .filter(peer => peer.title !== item.title)
    .slice(0, 4)
    .map(peer => peer.title)

  return {
    slug,
    title: item.title,
    icon: item.icon,
    description: descriptionByTitle[item.title] ?? defaultDescription,
    features: [item.title, ...peerFeatures],
    image: serviceImages[index % serviceImages.length] ?? serviceImages[0]!,
    gallery: serviceGallery
  }
})

export const getServiceBySlug = (slug: string) =>
  serviceDetails.find(item => item.slug === slug)

export const serviceDetailBreadcrumbs = (title: string): BreadcrumbItem[] => [
  { label: 'Home', to: '/' },
  { label: 'Services', to: '/services' },
  { label: title }
]
