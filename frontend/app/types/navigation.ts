export interface CmsNavigationItem {
  id: number
  label: string
  url: string
  target?: string | null
  icon?: string | null
  image?: string | null
  parent_id?: number | null
  sort_order?: number
  show_on_header?: boolean
  show_on_footer?: boolean
  seo?: {
    title?: string | null
    description?: string | null
  }
}

export interface NavLink {
  id?: number
  label: string
  to: string
  target?: string
  external?: boolean
  children?: boolean
}
