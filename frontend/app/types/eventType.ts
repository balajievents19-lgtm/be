export interface EventType {
  id: number
  name: string
  slug: string
  icon?: string | null
  sort_order?: number
}

export interface EventTypeListResponse {
  data: EventType[]
}
