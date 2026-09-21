<script setup lang="ts">
/**
 * Single shared Login modal (Master #loginModal visual reference).
 * Mounted once from app.vue — never inside v-for / cards.
 */
const {
  modal,
  closeModal,
  switchToRegister,
  login,
  oauthRedirectUrl,
  consumeReturnTo
} = useCustomerAuth()

const open = computed({
  get: () => modal.value === 'login',
  set: (v: boolean) => {
    if (!v) {
      closeModal()
    }
  }
})

const form = reactive({
  login: '',
  password: '',
  remember: true
})

const error = ref('')
const pending = ref(false)

const onSubmit = async () => {
  error.value = ''
  pending.value = true
  try {
    await login({
      login: form.login,
      password: form.password,
      remember: form.remember
    })
    closeModal()
    const returnTo = consumeReturnTo()
    if (returnTo) {
      await navigateTo(returnTo)
    }
  } catch (e: unknown) {
    const err = e as { data?: { message?: string, errors?: Record<string, string[]> } }
    error.value = err?.data?.errors?.login?.[0]
      || err?.data?.message
      || 'These credentials do not match our records.'
    if ((err as { data?: { code?: string } })?.data?.code === 'email_verification_required') {
      error.value = 'Verify your email address before signing in. Check your inbox for the verification code.'
    }
  } finally {
    pending.value = false
  }
}

const goForgot = async () => {
  closeModal()
  await navigateTo('/forgot-password')
}
</script>

<template>
  <UModal
    v-model:open="open"
    :ui="{ content: 'max-w-[860px] w-[calc(100%-1.5rem)] overflow-hidden rounded-sm p-0' }"
  >
    <template #content>
      <div class="relative flex max-h-[90vh] flex-col overflow-y-auto bg-white md:flex-row md:overflow-hidden">
        <button
          type="button"
          class="absolute top-2 right-2 z-20 flex size-9 items-center justify-center rounded-full bg-black/50 text-xl text-white hover:bg-brand-500"
          aria-label="Close login"
          @click="closeModal"
        >
          ×
        </button>

        <div class="hidden w-[42%] shrink-0 bg-[#1a1a2e] md:block">
          <img
            src="/images/auth/login-leftImg.png"
            alt=""
            class="h-full min-h-[420px] w-full object-cover"
          >
        </div>

        <div class="flex flex-1 flex-col justify-center px-6 py-8 sm:px-10">
          <h1 class="m-0 font-['Domine',Georgia,serif] text-[28px] font-bold text-[#333]">
            Login
          </h1>

          <div class="mt-5 flex flex-col gap-2 sm:flex-row">
            <a
              :href="oauthRedirectUrl('facebook')"
              class="inline-flex flex-1 items-center justify-center gap-2 rounded-sm bg-[#3b5998] px-3 py-2.5 text-sm text-white no-underline hover:opacity-90"
            >
              <span
                class="icon icon-facebook"
                aria-hidden="true"
              />
              Sign in with Facebook
            </a>
            <a
              :href="oauthRedirectUrl('google')"
              class="inline-flex flex-1 items-center justify-center gap-2 rounded-sm bg-[#dd4b39] px-3 py-2.5 text-sm text-white no-underline hover:opacity-90"
            >
              <span
                class="icon icon-google-plus"
                aria-hidden="true"
              />
              Sign in with Google
            </a>
          </div>

          <div class="relative my-5 text-center text-xs tracking-widest text-[#999] uppercase">
            <span class="relative z-[1] bg-white px-3">OR</span>
            <span
              class="absolute top-1/2 right-0 left-0 z-0 border-t border-[#e5e5e5]"
              aria-hidden="true"
            />
          </div>

          <form
            class="flex flex-col gap-3"
            @submit.prevent="onSubmit"
          >
            <label class="block">
              <span class="sr-only">Email or username</span>
              <input
                v-model="form.login"
                type="text"
                required
                autocomplete="username"
                placeholder="Email, username or mobile"
                class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm text-[#333] outline-none focus:border-brand-500"
              >
            </label>
            <label class="block">
              <span class="sr-only">Password</span>
              <input
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Password"
                class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm text-[#333] outline-none focus:border-brand-500"
              >
            </label>

            <div class="flex items-center justify-between gap-3 text-sm">
              <label class="inline-flex items-center gap-2 text-[#555]">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  class="accent-brand-500"
                >
                Remember me
              </label>
              <button
                type="button"
                class="text-brand-500 hover:underline"
                @click="goForgot"
              >
                Forgot password?
              </button>
            </div>

            <p
              v-if="error"
              class="m-0 text-sm text-red-600"
              role="alert"
            >
              {{ error }}
            </p>

            <button
              type="submit"
              class="mt-1 rounded-sm border border-brand-500 bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-60"
              :disabled="pending"
            >
              {{ pending ? 'Signing in…' : 'Login' }}
            </button>
          </form>

          <p class="mt-5 mb-0 text-center text-sm text-[#555]">
            Haven’t signed up yet?
            <button
              type="button"
              class="font-semibold text-brand-500 hover:underline"
              @click="switchToRegister"
            >
              Sign Up
            </button>
          </p>
        </div>
      </div>
    </template>
  </UModal>
</template>
