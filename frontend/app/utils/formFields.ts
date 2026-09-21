/** Format ISO yyyy-mm-dd for display (dd-mm-yyyy). */
export const formatEventDateDisplay = (iso: string | null | undefined): string => {
  if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) {
    return ''
  }

  const [year, month, day] = iso.split('-')
  return `${day}-${month}-${year}`
}

/** True when ISO date is strictly before today's local calendar day. */
export const isPastEventDate = (iso: string, now = new Date()): boolean => {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) {
    return true
  }

  const parts = iso.split('-').map(Number)
  const y = parts[0] ?? 0
  const m = parts[1] ?? 1
  const d = parts[2] ?? 1
  const date = new Date(y, m - 1, d)
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  return date < today
}

export const buildProgramLocationLabel = (parts: {
  name?: string | null
  address?: string | null
  city?: string | null
  state?: string | null
  pincode?: string | null
}): string => {
  const chunks = [
    parts.name?.trim(),
    parts.address?.trim(),
    [parts.city, parts.state, parts.pincode].filter(Boolean).join(', ').trim()
  ].filter(Boolean) as string[]

  return chunks.join(' — ')
}

/** Customer mobile / service inquiry: exactly 10 digits, any starting digit. */
export const isExactTenDigitMobile = (value: string): boolean => /^[0-9]{10}$/.test(value)

/** Enquiry phone: 10–15 digits after stripping formatting. */
export const isValidEnquiryPhone = (value: string): boolean => {
  const trimmed = value.trim()
  if (!/^\+?[0-9][0-9\s\-()]{8,28}$/.test(trimmed)) {
    return false
  }

  const digits = trimmed.replace(/\D/g, '')
  return digits.length >= 10 && digits.length <= 15
}
