import type { ContactBox } from '~/data/contact'
import type { SiteSettings } from '~/types/home'

/** Build contact info cards from settings — no hardcoded phones/address. */
export const contactBoxesFromSettings = (settings: SiteSettings | null | undefined): ContactBox[] => {
  if (!settings?.contact) {
    return []
  }

  const c = settings.contact
  const boxes: ContactBox[] = []

  const phones: { label: string, href: string }[] = []
  if (c.phone) {
    phones.push({ label: c.phone, href: `tel:${c.phone.replace(/[^\d+]/g, '')}` })
  }
  if (c.alternate_phone) {
    phones.push({
      label: c.alternate_phone,
      href: `tel:${c.alternate_phone.replace(/[^\d+]/g, '')}`
    })
  }
  if (phones.length) {
    boxes.push({ icon: 'icon-phone', type: 'phones', phones })
  }

  if (c.address) {
    boxes.push({ icon: 'icon-location-1', type: 'address', address: c.address })
  }

  if (c.email) {
    boxes.push({
      icon: 'icon-message',
      type: 'email',
      lines: [
        {
          label: 'Email - ',
          text: c.email,
          href: `mailto:${c.email}`
        }
      ]
    })
  }

  return boxes
}

export const formatBlogDate = (iso: string | null | undefined): string => {
  if (!iso) {
    return ''
  }
  const date = new Date(iso)
  if (Number.isNaN(date.getTime())) {
    return ''
  }
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
