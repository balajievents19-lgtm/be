<script setup lang="ts">
const route = useRoute()
const { data: settings } = useSettings()
const { resetPassword, openLogin } = useCustomerAuth()

useSeoMeta({
  title: 'Reset Password | Balaji Royal Events',
  robots: 'noindex, nofollow'
})

const form = reactive({
  email: String(route.query.email || ''),
  token: String(route.query.token || ''),
  password: '',
  password_confirmation: ''
})

const message = ref('')
const error = ref('')
const pending = ref(false)

const onSubmit = async () => {
  pending.value = true
  error.value = ''
  message.value = ''
  try {
    const res = await resetPassword({ ...form })
    message.value = res.message
    setTimeout(() => openLogin(), 800)
  } catch (e: unknown) {
    const err = e as { data?: { message?: string, errors?: Record<string, string[]> } }
    error.value = err?.data?.errors?.email?.[0]
      || err?.data?.errors?.password?.[0]
      || err?.data?.message
      || 'Unable to reset password.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="Reset Password"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Reset Password' }]"
      />
      <section class="py-12 sm:py-16">
        <UContainer class="mx-auto max-w-[480px]">
          <div class="rounded-sm border border-[#eee] bg-white p-6 shadow-[0_8px_24px_rgba(16,15,15,0.06)] sm:p-8">
            <h2 class="m-0 font-['Domine',Georgia,serif] text-xl font-bold text-[#333]">
              Choose a new password
            </h2>
            <form
              class="mt-6 flex flex-col gap-3"
              @submit.prevent="onSubmit"
            >
              <input
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                placeholder="Email"
                class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
              >
              <input
                v-model="form.token"
                type="text"
                required
                placeholder="Reset token"
                class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
              >
              <input
                v-model="form.password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="New password"
                class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
              >
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirm new password"
                class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
              >
              <button
                type="submit"
                class="rounded-sm bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-60"
                :disabled="pending"
              >
                {{ pending ? 'Saving…' : 'Reset password' }}
              </button>
            </form>
            <p
              v-if="message"
              class="mt-4 text-sm text-green-700"
              role="status"
            >
              {{ message }}
            </p>
            <p
              v-if="error"
              class="mt-4 text-sm text-red-600"
              role="alert"
            >
              {{ error }}
            </p>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
