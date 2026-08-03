export interface StaticHeroSlide {
  src: string
  alt: string
}

export interface EventItem {
  title: string
  caption: string
  image: string
  description: string
  to: string
}

export interface TestimonialItem {
  name: string
  quote: string
  avatar: string
}

export interface SuccessStoryItem {
  name: string
  image: string
  text: string
}

export interface NewsItem {
  title: string
  author: string
  date: string
  excerpt: string
  image?: string
  variant: 'featured' | 'text' | 'side'
  to: string
}

export interface FooterUpdateItem {
  image: string
  text: string
  to: string
}

export interface FooterLink {
  label: string
  to: string
}

export interface SocialLink {
  icon: string
  label: string
  href: string
}

export const heroSlides: StaticHeroSlide[] = [
  {
    src: '/images/banner/slider-img.jpg',
    alt: 'Balaji Events celebration'
  },
  {
    src: '/images/banner/slider-img2.jpg',
    alt: 'Balaji Events wedding setup'
  },
  {
    src: '/images/banner/slider-img3.jpg',
    alt: 'Balaji Events party venue'
  }
]

export const overviewEvents: EventItem[] = [
  {
    title: 'Event Planner',
    caption: 'Event Planner',
    image: '/images/event/event-img1.jpg',
    description: 'Balaji Events is trusted wedding management company in india.We were Provide many differnt service in Rajasthan. We promise to set the perfect pitch for to-be and groom to celebrate their special day in grand way.',
    to: '/services'
  },
  {
    title: 'Corporate Events',
    caption: 'Corporate Events',
    image: '/images/event/event-img2.jpg',
    description: 'The corporate events includes planning and organizing different types of nts can be for a variety of reasons. Some of these include – conferences, team dinners, orientation for new joinees, farewell parties, trade shows, exhibitions, awards and incentive programs etc.',
    to: '/services'
  },
  {
    title: 'Birthday Party',
    caption: 'Birthday Party',
    image: '/images/event/event-img3.jpg',
    description: 'In This birthday party balji events provide Special Movement, Ballon Decsheets, Dinner, Special Cake, mmusical instruments, and much more variety.',
    to: '/services'
  },
  {
    title: 'Event Planner',
    caption: 'Event Planner',
    image: '/images/event/event-img1.jpg',
    description: 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s ype specimen book. It has survived not only five centuries,',
    to: '/services'
  },
  {
    title: 'Corporate Events',
    caption: 'Corporate Events',
    image: '/images/event/event-img2.jpg',
    description: 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s ype specimen book. It has survived not only five centuries,',
    to: '/services'
  },
  {
    title: 'Birthday Party',
    caption: 'Birthday Party',
    image: '/images/event/event-img3.jpg',
    description: 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s ype specimen book. It has survived not only five centuries,',
    to: '/services'
  }
]

export const testimonials: TestimonialItem[] = [
  {
    name: 'John Doe',
    avatar: '/images/user/friend-img.png',
    quote: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, an unknown printer took a galley of type and scrambled it type specimen book.'
  },
  {
    name: 'John Doe',
    avatar: '/images/user/friend-img.png',
    quote: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, an unknown printer took a galley of type and scrambled it type specimen book.'
  }
]

export const successStories: SuccessStoryItem[] = [
  {
    name: 'Larry Cook',
    image: '/images/user/story-img1.png',
    text: 'We have 20 years experience planning and organizing beautiful weddings and events. We have built up excellent relationships with the most professional suppliers on the coast to help with your every desire. From a small intimate gathering to a more luxurious wedding day we can help you make your Dream Wedding a reality.'
  },
  {
    name: 'Stacy Benjamin',
    image: '/images/user/story-img2.png',
    text: 'We have 20 years experience planning and organizing beautiful weddings and events. We have built up excellent relationships with the most professional suppliers on the coast to help with your every desire. From a small intimate gathering to a more luxurious wedding day we can help you make your Dream Wedding a reality.'
  }
]

export const latestNews: NewsItem[] = [
  {
    title: 'Post with Image Here',
    author: 'Rashed kabir',
    date: '24 Feb, 2014',
    excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    image: '/images/news/news-img1.png',
    variant: 'featured',
    to: '/blog'
  },
  {
    title: 'Post with Image Here',
    author: 'Rashed kabir',
    date: '24 Feb, 2014',
    excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    variant: 'text',
    to: '/blog'
  },
  {
    title: 'Post with Image Here',
    author: 'Rashed kabir',
    date: '24 Feb, 2014',
    excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    variant: 'text',
    to: '/blog'
  },
  {
    title: 'Post with Image Here',
    author: 'Rashed kabir',
    date: '24 Feb, 2014',
    excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    image: '/images/news/news-img2.png',
    variant: 'side',
    to: '/blog'
  }
]

export const footerUpdates: FooterUpdateItem[] = [
  {
    image: '/images/event/update-img1.png',
    text: 'Lorem ipsum is a dummy text full service industrial design.',
    to: '/blog'
  },
  {
    image: '/images/event/update-img2.png',
    text: 'Integrated Design Systems is a full-service industrial design.',
    to: '/blog'
  },
  {
    image: '/images/event/update-img3.png',
    text: 'when an unknown printer took a galley of type and specimen book.',
    to: '/blog'
  }
]

export const footerCompanyLinks: FooterLink[] = [
  { label: 'About Us', to: '/about' },
  { label: 'Privacy Policy', to: '/privacy-policy' },
  { label: 'Careers', to: '/careers' },
  { label: 'Blogs', to: '/blog' },
  { label: 'Contact Us', to: '/contact' }
]

export const footerSocialLinks: SocialLink[] = [
  { icon: 'icon-facebook', label: 'Facebook', href: '#' },
  { icon: 'icon-twitter', label: 'Twitter', href: '#' },
  { icon: 'icon-linkedin', label: 'LinkedIn', href: '#' },
  { icon: 'icon-skype', label: 'Skype', href: '#' },
  { icon: 'icon-google-plus', label: 'Google Plus', href: '#' },
  { icon: 'icon-play', label: 'YouTube', href: '#' }
]

export const siteContact = {
  email: 'balajievents19@gmail.com',
  phoneDisplay: '+91-94625-77065',
  phoneHref: 'tel:+919462577065',
  address: 'Golai Mode, Road No.3, Near : J.M Bajaj Bike Agency Jhunjhunu (Rajasthan)'
} as const
