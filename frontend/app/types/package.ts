export interface ServicePackage {
  id: number
  name: string
  slug: string
  summary: string | null
  description: string | null
  price_label: string | null
  price_amount: string | number | null
  currency: string | null
  features: string[]
  is_featured: boolean
  image: string | null
  service_id: number | null
  service_category_id: number | null
  sort_order: number
  seo?: {
    title: string | null
    description: string | null
  }
}
