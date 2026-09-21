export interface ContactPayload {
  name: string
  mobile: string
  email?: string | null
  subject?: string | null
  message: string
  service_interested?: string | null
  event_date?: string | null
  event_location?: string | null
  event_type_id?: number | null
  company?: string | null
  budget?: string | null
  source?: string | null
  website?: string
}

export interface NewsletterPayload {
  first_name?: string | null
  last_name?: string | null
  email: string
  website?: string
}

/** Shared public write helpers — Sanctum CSRF + cookies, same as customer auth. */
export const usePublicForms = () => {
  const submitContact = (payload: ContactPayload) =>
    customerFetch('/contact', {
      method: 'POST',
      body: {
        ...payload,
        website: ''
      }
    })

  const submitNewsletter = (payload: NewsletterPayload) =>
    customerFetch('/newsletter', {
      method: 'POST',
      body: {
        ...payload,
        website: ''
      }
    })

  return {
    submitContact,
    submitNewsletter
  }
}
