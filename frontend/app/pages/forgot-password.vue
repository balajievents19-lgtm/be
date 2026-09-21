<script setup lang="ts">
const { data: settings } = useSettings()
const { forgotPassword, openLogin } = useCustomerAuth()

useSeoMeta({
  title: 'Forgot Password | Balaji Royal Events',
  robots: 'noindex, nofollow'
})

const login = ref('')
const message = ref('')
const pending = ref(false)

const onSubmit = async () => {
  pending.value = true
  message.value = ''
  try {
    const res = await forgotPassword(login.value)
    message.value = res.message
  } catch {
    message.value = 'If that account exists, we sent password reset instructions.'
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
        title="Forgot Password"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Forgot Password' }]"
      />
      <section class="py-12 sm:py-16">
        <UContainer class="mx-auto max-w-[480px]">
          <div class="rounded-sm border border-[#eee] bg-white p-6 shadow-[0_8px_24px_rgba(16,15,15,0.06)] sm:p-8">
            <h2 class="m-0 font-['Domine',Georgia,serif] text-xl font-bold text-[#333]">
              Reset your password
            </h2>
            <p class="mt-3 mb-0 text-sm leading-6 text-[#555]">
              Enter your email or verified mobile number. If the account exists, we will send reset instructions.
            </p>
            <form
              class="mt-6 flex flex-col gap-3"
              @submit.prevent="onSubmit"
            >
              <label class="block">
                <span class="mb-1 block text-xs font-medium tracking-wide text-[#666] uppercase">Email or mobile</span>
                <input
                  v-model="login"
                  type="text"
                  required
                  autocomplete="username"
                  placeholder="Email or mobile number"
                  class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
                >
              </label>
              <button
                type="submit"
                class="rounded-sm bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-60"
                :disabled="pending"
              >
                {{ pending ? 'Sending…' : 'Send reset link' }}
              </button>
            </form>
            <p
              v-if="message"
              class="mt-4 text-sm text-[#555]"
              role="status"
            >
              {{ message }}
            </p>
            <button
              type="button"
              class="mt-6 text-sm font-medium text-brand-500 hover:underline"
              @click="openLogin()"
            >
              Back to Login
            </button>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
