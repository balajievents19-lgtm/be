<script setup lang="ts">
const route = useRoute()
const { data: settings } = useSettings()
const {
  customer,
  loaded,
  refresh,
  logout,
  openLogin,
  changePassword,
  changeMobile,
  changeEmail,
  verifyEmailChange,
  resendEmailVerification,
  listSessions,
  logoutOtherSessions,
  listSocialAccounts,
  unlinkSocial,
  confirmPassword,
  oauthRedirectUrl
} = useCustomerAuth()

useSeoMeta({
  title: 'My Account | Balaji Royal Events',
  robots: 'noindex, nofollow'
})

const notice = ref('')
const error = ref('')
const sessions = ref<{ id: string, ip_address: string | null, user_agent: string | null, last_activity_at: string | null, is_current: boolean }[]>([])
const socials = ref<{ id: number, provider: string, provider_email: string | null }[]>([])

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const mobileForm = reactive({ current_password: '', phone: '' })
const emailForm = reactive({ current_password: '', email: '' })
const reauthPassword = ref('')
const pending = ref(false)

const loadSecurity = async () => {
  if (!customer.value) {
    return
  }
  try {
    const [s, a] = await Promise.all([listSessions(), listSocialAccounts()])
    sessions.value = s.data
    socials.value = a.data
  } catch {
    sessions.value = []
    socials.value = []
  }
}

onMounted(async () => {
  if (!loaded.value) {
    await refresh()
  }
  if (!customer.value) {
    openLogin('/account')
    return
  }
  await loadSecurity()

  if (route.query.oauth === 'success') {
    notice.value = 'You are signed in.'
  }
  if (route.query.verified === '1') {
    notice.value = 'Your email is verified. You can sign in.'
  }

  const emailToken = typeof route.query.verify_email === 'string' ? route.query.verify_email : ''
  if (emailToken) {
    try {
      const res = await verifyEmailChange(emailToken)
      notice.value = res.message
    } catch {
      error.value = 'This email confirmation link is invalid or has expired.'
    }
  }
})

const onLogout = async () => {
  await logout()
  await navigateTo('/')
}

const run = async (fn: () => Promise<void>) => {
  pending.value = true
  error.value = ''
  notice.value = ''
  try {
    await fn()
  } catch (e: unknown) {
    const err = e as { data?: { message?: string, errors?: Record<string, string[]> } }
    const first = err?.data?.errors ? Object.values(err.data.errors)[0]?.[0] : undefined
    error.value = first || err?.data?.message || 'Unable to save those changes.'
  } finally {
    pending.value = false
  }
}

const submitPassword = () => run(async () => {
  const res = await changePassword(passwordForm)
  notice.value = res.message
  passwordForm.current_password = ''
  passwordForm.password = ''
  passwordForm.password_confirmation = ''
})

const submitMobile = () => run(async () => {
  if (!isExactTenDigitMobile(mobileForm.phone)) {
    error.value = 'Enter exactly 10 digits with no +91, spaces, or punctuation.'
    return
  }
  const res = await changeMobile({
    current_password: mobileForm.current_password,
    phone: mobileForm.phone
  })
  notice.value = res.message
  await refresh()
})

const submitEmail = () => run(async () => {
  const res = await changeEmail(emailForm)
  notice.value = res.message
  await refresh()
})

const resendSignupEmail = () => run(async () => {
  if (!customer.value?.email) {
    return
  }
  const res = await resendEmailVerification(customer.value.email)
  notice.value = res.message
})

const confirmThenUnlink = (provider: string) => run(async () => {
  await confirmPassword(reauthPassword.value)
  await unlinkSocial(provider)
  notice.value = `${provider} disconnected.`
  await loadSecurity()
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="My Account"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Account' }]"
      />
      <section class="py-12 sm:py-16">
        <UContainer class="mx-auto max-w-[720px]">
          <div
            v-if="!customer"
            class="rounded-sm border border-[#eee] bg-[#fafafa] px-6 py-10 text-center"
          >
            <p class="m-0 text-sm text-[#555]">
              Please log in to view your account.
            </p>
            <button
              type="button"
              class="mt-6 rounded-sm border border-brand-500 bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600"
              @click="openLogin('/account')"
            >
              Login
            </button>
          </div>
          <div
            v-else
            class="space-y-6"
          >
            <p
              v-if="notice"
              class="m-0 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
              role="status"
            >
              {{ notice }}
            </p>
            <p
              v-if="error"
              class="m-0 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
              role="alert"
            >
              {{ error }}
            </p>

            <div class="rounded-sm border border-[#eee] bg-white p-6 shadow-[0_8px_24px_rgba(16,15,15,0.06)] sm:p-8">
              <div class="flex items-center gap-4">
                <img
                  v-if="customer.avatar"
                  :src="customer.avatar"
                  alt=""
                  class="size-16 rounded-full object-cover"
                >
                <div
                  v-else
                  class="flex size-16 items-center justify-center rounded-full bg-brand-500/10 font-['Domine',Georgia,serif] text-xl font-bold text-brand-500"
                  aria-hidden="true"
                >
                  {{ (customer.name || customer.username || customer.email || 'C').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h2 class="m-0 font-['Domine',Georgia,serif] text-xl font-bold text-[#333]">
                    {{ customer.name || customer.username || 'Customer' }}
                  </h2>
                  <p class="mt-1 mb-0 text-sm text-[#666]">
                    {{ customer.email }}
                  </p>
                </div>
              </div>
              <dl class="mt-6 grid gap-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-[#f0f0f0] pb-2">
                  <dt class="text-[#888]">
                    Username
                  </dt>
                  <dd class="m-0 font-medium text-[#333]">
                    {{ customer.username || '—' }}
                  </dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-[#f0f0f0] pb-2">
                  <dt class="text-[#888]">
                    Phone
                  </dt>
                  <dd class="m-0 font-medium text-[#333]">
                    {{ customer.phone_masked || customer.phone || '—' }}
                  </dd>
                </div>
              </dl>
              <div class="mt-8 flex flex-wrap gap-3">
                <NuxtLink
                  to="/gallery"
                  class="inline-flex items-center justify-center rounded-sm border border-[#ddd] bg-white px-5 py-2.5 text-sm font-semibold text-[#333] transition-colors hover:border-brand-500 hover:text-brand-500"
                >
                  Browse Gallery
                </NuxtLink>
                <button
                  type="button"
                  class="rounded-sm border border-brand-500 bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600"
                  @click="onLogout"
                >
                  Logout
                </button>
              </div>
            </div>

            <div
              v-if="customer.needs_email_verification"
              class="rounded-sm border border-[#eee] bg-white p-6 sm:p-8"
            >
              <h3 class="m-0 font-['Domine',Georgia,serif] text-lg font-bold text-[#333]">
                Verify email
              </h3>
              <p class="mt-2 text-sm text-[#555]">
                Confirm your email address to finish setting up your account.
              </p>
              <button
                type="button"
                class="mt-4 rounded-sm bg-brand-500 px-4 py-2.5 text-sm text-white"
                :disabled="pending"
                @click="resendSignupEmail"
              >
                Resend verification email
              </button>
            </div>

            <div class="rounded-sm border border-[#eee] bg-white p-6 sm:p-8">
              <h3 class="m-0 font-['Domine',Georgia,serif] text-lg font-bold text-[#333]">
                Change password
              </h3>
              <form
                class="mt-4 flex flex-col gap-3"
                @submit.prevent="submitPassword"
              >
                <input
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  autocomplete="current-password"
                  placeholder="Current password"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <input
                  v-model="passwordForm.password"
                  type="password"
                  required
                  autocomplete="new-password"
                  placeholder="New password"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  autocomplete="new-password"
                  placeholder="Confirm new password"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <button
                  type="submit"
                  class="self-start rounded-sm bg-brand-500 px-4 py-2.5 text-sm text-white disabled:opacity-60"
                  :disabled="pending"
                >
                  Update password
                </button>
              </form>
            </div>

            <div class="rounded-sm border border-[#eee] bg-white p-6 sm:p-8">
              <h3 class="m-0 font-['Domine',Georgia,serif] text-lg font-bold text-[#333]">
                Change mobile
              </h3>
              <form
                class="mt-4 flex flex-col gap-3"
                @submit.prevent="submitMobile"
              >
                <input
                  v-model="mobileForm.current_password"
                  type="password"
                  required
                  placeholder="Current password"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <input
                  v-model="mobileForm.phone"
                  type="tel"
                  required
                  placeholder="New mobile number"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <button
                  type="submit"
                  class="self-start rounded-sm bg-brand-500 px-4 py-2.5 text-sm text-white disabled:opacity-60"
                  :disabled="pending"
                >
                  Update mobile
                </button>
              </form>
            </div>

            <div class="rounded-sm border border-[#eee] bg-white p-6 sm:p-8">
              <h3 class="m-0 font-['Domine',Georgia,serif] text-lg font-bold text-[#333]">
                Change email
              </h3>
              <form
                class="mt-4 flex flex-col gap-3"
                @submit.prevent="submitEmail"
              >
                <input
                  v-model="emailForm.current_password"
                  type="password"
                  required
                  placeholder="Current password"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <input
                  v-model="emailForm.email"
                  type="email"
                  required
                  placeholder="New email"
                  class="rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
                <button
                  type="submit"
                  class="self-start rounded-sm bg-brand-500 px-4 py-2.5 text-sm text-white disabled:opacity-60"
                  :disabled="pending"
                >
                  Send confirmation
                </button>
              </form>
            </div>

            <div class="rounded-sm border border-[#eee] bg-white p-6 sm:p-8">
              <h3 class="m-0 font-['Domine',Georgia,serif] text-lg font-bold text-[#333]">
                Connected accounts
              </h3>
              <div class="mt-4 flex flex-wrap gap-2">
                <a
                  :href="oauthRedirectUrl('google', true)"
                  class="rounded-sm bg-[#dd4b39] px-3 py-2 text-sm text-white no-underline"
                >Connect Google</a>
                <a
                  :href="oauthRedirectUrl('facebook', true)"
                  class="rounded-sm bg-[#3b5998] px-3 py-2 text-sm text-white no-underline"
                >Connect Facebook</a>
              </div>
              <ul class="mt-4 mb-0 list-none space-y-2 p-0 text-sm">
                <li
                  v-for="item in socials"
                  :key="item.id"
                  class="flex items-center justify-between border-b border-[#f0f0f0] py-2"
                >
                  <span class="capitalize">{{ item.provider }} {{ item.provider_email ? `· ${item.provider_email}` : '' }}</span>
                  <button
                    type="button"
                    class="text-red-600 hover:underline"
                    @click="confirmThenUnlink(item.provider)"
                  >
                    Disconnect
                  </button>
                </li>
              </ul>
              <input
                v-model="reauthPassword"
                type="password"
                placeholder="Confirm password to disconnect"
                class="mt-3 w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
              >
            </div>

            <div class="rounded-sm border border-[#eee] bg-white p-6 sm:p-8">
              <h3 class="m-0 font-['Domine',Georgia,serif] text-lg font-bold text-[#333]">
                Active sessions
              </h3>
              <ul class="mt-4 mb-4 list-none space-y-2 p-0 text-sm">
                <li
                  v-for="row in sessions"
                  :key="row.id"
                  class="border-b border-[#f0f0f0] pb-2"
                >
                  <strong>{{ row.is_current ? 'This device' : 'Other device' }}</strong>
                  <span class="block text-[#666]">{{ row.user_agent }}</span>
                  <span class="block text-xs text-[#888]">{{ row.ip_address }} · {{ row.last_activity_at }}</span>
                </li>
              </ul>
              <button
                type="button"
                class="rounded-sm border border-[#ddd] px-4 py-2.5 text-sm"
                :disabled="pending"
                @click="run(async () => { await logoutOtherSessions(); notice = 'Other devices signed out.'; await loadSecurity() })"
              >
                Logout other devices
              </button>
            </div>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
