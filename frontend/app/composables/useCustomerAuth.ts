export interface Customer {
  id: number
  name: string | null
  email: string
  username: string | null
  phone: string | null
  phone_masked?: string | null
  avatar: string | null
  email_verified_at?: string | null
  email_verified?: boolean
  last_login_at?: string | null
  pending_email?: string | null
  needs_email_verification?: boolean
}

export interface VerificationMeta {
  masked_email?: string
  resend_after?: number
  required?: boolean
  sent?: boolean
}

export interface CustomerSessionRow {
  id: string
  ip_address: string | null
  user_agent: string | null
  last_activity_at: string | null
  is_current: boolean
}

export interface SocialAccountRow {
  id: number
  provider: string
  provider_email: string | null
  provider_name: string | null
  created_at: string | null
}

export type AuthModalView = 'login' | 'register' | null

/** Laravel origin without /api — used for Sanctum CSRF cookie. */
export const useLaravelOrigin = () => {
  const base = useApiBase()
  return base.replace(/\/api\/?$/, '')
}

const ensureCsrfCookie = async () => {
  const origin = useLaravelOrigin()
  await $fetch(`${origin}/sanctum/csrf-cookie`, {
    credentials: 'include',
    method: 'GET'
  })
}

const readXsrfToken = (): string | undefined => {
  if (!import.meta.client) {
    return undefined
  }
  const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
  return match?.[1] ? decodeURIComponent(match[1]) : undefined
}

/** Credentialed customer API calls (client-only session). Never uses SSR secret. */
export const customerFetch = async <T>(
  path: string,
  opts: Parameters<typeof $fetch<T>>[1] = {}
): Promise<T> => {
  const base = useApiBase()
  const method = String(opts?.method || 'GET').toUpperCase()

  if (method !== 'GET' && method !== 'HEAD') {
    await ensureCsrfCookie()
  }

  const xsrf = readXsrfToken()
  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(opts?.headers as Record<string, string> | undefined)
  }
  if (xsrf) {
    headers['X-XSRF-TOKEN'] = xsrf
  }

  return $fetch<T>(`${base}${path.startsWith('/') ? path : `/${path}`}`, {
    ...opts,
    credentials: 'include',
    headers
  }) as Promise<T>
}

export const useCustomerAuth = () => {
  const customer = useState<Customer | null>('customer-auth-user', () => null)
  const loaded = useState('customer-auth-loaded', () => false)
  const modal = useState<AuthModalView>('customer-auth-modal', () => null)
  const authReturnTo = useState<string | null>('customer-auth-return-to', () => null)

  const openLogin = (returnTo?: string) => {
    if (returnTo) {
      authReturnTo.value = returnTo
    }
    modal.value = 'login'
  }

  const openRegister = (returnTo?: string) => {
    if (returnTo) {
      authReturnTo.value = returnTo
    }
    modal.value = 'register'
  }

  const closeModal = () => {
    modal.value = null
  }

  const switchToRegister = () => {
    modal.value = 'register'
  }

  const switchToLogin = () => {
    modal.value = 'login'
  }

  const refresh = async () => {
    if (!import.meta.client) {
      return null
    }
    try {
      const res = await customerFetch<{ data: Customer }>('/customer/me')
      customer.value = res.data
      loaded.value = true
      return res.data
    } catch {
      customer.value = null
      loaded.value = true
      return null
    }
  }

  const register = async (payload: Record<string, unknown>) => {
    return customerFetch<{ data: Customer, message: string, verification?: VerificationMeta }>('/customer/register', {
      method: 'POST',
      body: payload
    })
  }

  const verifyEmail = async (payload: { email: string, otp: string }) => {
    const res = await customerFetch<{ data?: Customer, message: string }>('/customer/email/verify', {
      method: 'POST',
      body: payload
    })
    if (res.data) {
      customer.value = res.data
      loaded.value = true
    }
    return res
  }

  const resendEmailVerification = async (email: string) => {
    return customerFetch<{ message: string, verification?: VerificationMeta }>('/customer/email/resend', {
      method: 'POST',
      body: { email }
    })
  }

  const changePassword = async (payload: {
    current_password: string
    password: string
    password_confirmation: string
  }) => {
    return customerFetch<{ message: string }>('/customer/change-password', {
      method: 'POST',
      body: payload
    })
  }

  const changeMobile = async (payload: { current_password: string, phone: string }) => {
    return customerFetch<{ message: string, data: Customer }>('/customer/change-mobile', {
      method: 'POST',
      body: payload
    })
  }

  const changeEmail = async (payload: { current_password: string, email: string }) => {
    return customerFetch<{ message: string, data: Customer }>('/customer/change-email', {
      method: 'POST',
      body: payload
    })
  }

  const verifyEmailChange = async (token: string) => {
    const res = await customerFetch<{ message: string, data: Customer }>('/customer/verify-email', {
      method: 'POST',
      body: { token }
    })
    customer.value = res.data
    return res
  }

  const listSessions = async () => {
    return customerFetch<{ data: CustomerSessionRow[] }>('/customer/sessions')
  }

  const logoutOtherSessions = async () => {
    return customerFetch<{ message: string, count: number }>('/customer/sessions/others', {
      method: 'DELETE'
    })
  }

  const endSession = async (id: string) => {
    return customerFetch<{ message: string }>(`/customer/sessions/${id}`, {
      method: 'DELETE'
    })
  }

  const listSocialAccounts = async () => {
    return customerFetch<{ data: SocialAccountRow[] }>('/customer/social-accounts')
  }

  const unlinkSocial = async (provider: string) => {
    return customerFetch<{ message: string }>(`/customer/social-accounts/${provider}`, {
      method: 'DELETE'
    })
  }

  const confirmPassword = async (password: string) => {
    return customerFetch<{ message: string }>('/customer/reauthenticate', {
      method: 'POST',
      body: { password }
    })
  }

  const oauthProviders = async () => {
    return customerFetch<{ data: { google: boolean, facebook: boolean, instagram: boolean } }>('/customer/oauth/providers')
  }

  const login = async (payload: { login: string, password: string, remember?: boolean }) => {
    const res = await customerFetch<{ data: Customer, message: string }>('/customer/login', {
      method: 'POST',
      body: payload
    })
    customer.value = res.data
    loaded.value = true
    return res
  }

  const logout = async () => {
    try {
      await customerFetch('/customer/logout', { method: 'POST' })
    } finally {
      customer.value = null
    }
  }

  const forgotPassword = async (login: string) => {
    return customerFetch<{ message: string }>('/customer/forgot-password', {
      method: 'POST',
      body: { login }
    })
  }

  const resetPassword = async (payload: {
    email: string
    token: string
    password: string
    password_confirmation: string
  }) => {
    return customerFetch<{ message: string }>('/customer/reset-password', {
      method: 'POST',
      body: payload
    })
  }

  const oauthRedirectUrl = (provider: 'google' | 'facebook', link = false) => {
    const base = useApiBase()
    const extra = link ? '?link=1' : ''
    return `${base}/customer/oauth/${provider}/redirect${extra}`
  }

  const consumeReturnTo = () => {
    const value = authReturnTo.value
    authReturnTo.value = null
    return value
  }

  return {
    customer,
    loaded,
    modal,
    openLogin,
    openRegister,
    closeModal,
    switchToLogin,
    switchToRegister,
    refresh,
    register,
    verifyEmail,
    resendEmailVerification,
    login,
    logout,
    forgotPassword,
    resetPassword,
    changePassword,
    changeMobile,
    changeEmail,
    verifyEmailChange,
    listSessions,
    logoutOtherSessions,
    endSession,
    listSocialAccounts,
    unlinkSocial,
    confirmPassword,
    oauthProviders,
    oauthRedirectUrl,
    consumeReturnTo,
    ensureCsrfCookie
  }
}
