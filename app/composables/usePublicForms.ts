export interface ContactPayload {
  name: string
  mobile: string
  email?: string | null
  subject?: string | null
  message: string
  service_interested?: string | null
  event_date?: string | null
  company?: string | null
  budget?: string | null
  website?: string
}

export interface NewsletterPayload {
  first_name?: string | null
  last_name?: string | null
  email: string
  website?: string
}

/** Shared public write helpers — single API base, no duplicate clients. */
export const usePublicForms = () => {
  const base = useApiBase()

  const submitContact = (payload: ContactPayload) =>
    $fetch(`${base}/contact`, {
      method: 'POST',
      body: {
        ...payload,
        website: ''
      }
    })

  const submitNewsletter = (payload: NewsletterPayload) =>
    $fetch(`${base}/newsletter`, {
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
