export interface GalleryImage {
  src: string
  alt: string
}

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

/** Master home3 gallery assets (gallery-section), plus gallery1–6 from master gallery-img. */
export const galleryImages: GalleryImage[] = [
  { src: '/images/gallery/home3-galleryImg1.jpg', alt: 'Gallery image 1' },
  { src: '/images/gallery/home3-galleryImg2.jpg', alt: 'Gallery image 2' },
  { src: '/images/gallery/home3-galleryImg3.jpg', alt: 'Gallery image 3' },
  { src: '/images/gallery/home3-galleryImg4.jpg', alt: 'Gallery image 4' },
  { src: '/images/gallery/home3-galleryImg5.jpg', alt: 'Gallery image 5' },
  { src: '/images/gallery/home3-galleryImg6.jpg', alt: 'Gallery image 6' },
  { src: '/images/gallery/home3-galleryImg7.jpg', alt: 'Gallery image 7' },
  { src: '/images/gallery/home3-galleryImg8.jpg', alt: 'Gallery image 8' },
  { src: '/images/gallery/home3-galleryImg9.jpg', alt: 'Gallery image 9' },
  { src: '/images/gallery/home3-galleryImg10.jpg', alt: 'Gallery image 10' },
  { src: '/images/gallery/gallery1.jpg', alt: 'Gallery image 11' },
  { src: '/images/gallery/gallery2.jpg', alt: 'Gallery image 12' },
  { src: '/images/gallery/gallery3.jpg', alt: 'Gallery image 13' },
  { src: '/images/gallery/gallery4.jpg', alt: 'Gallery image 14' },
  { src: '/images/gallery/gallery5.jpg', alt: 'Gallery image 15' },
  { src: '/images/gallery/gallery6.jpg', alt: 'Gallery image 16' }
]
