<script setup lang="ts">
/**
 * Single shared Registration modal (Master #registrationModal visual reference).
 * Mounted once from app.vue — never inside v-for / cards.
 */
const {
  modal,
  closeModal,
  switchToLogin,
  register,
  verifyEmail,
  resendEmailVerification,
  consumeReturnTo
} = useCustomerAuth()

const open = computed({
  get: () => modal.value === 'register',
  set: (v: boolean) => {
    if (!v) {
      closeModal()
    }
  }
})

const form = reactive({
  name: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
  phone: '',
  terms: false
})

const error = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const pending = ref(false)
const step = ref<'form' | 'verify'>('form')
const otp = ref('')
const verification = ref<{ masked_email?: string, resend_after?: number, sent?: boolean }>({})
const resendIn = ref(0)
let resendTimer: ReturnType<typeof setInterval> | null = null

watch(open, (isOpen) => {
  if (!isOpen) {
    step.value = 'form'
    otp.value = ''
    error.value = ''
  }
})

const startResendCountdown = (seconds: number) => {
  resendIn.value = seconds
  if (resendTimer) {
    clearInterval(resendTimer)
  }
  resendTimer = setInterval(() => {
    resendIn.value = Math.max(0, resendIn.value - 1)
    if (resendIn.value <= 0 && resendTimer) {
      clearInterval(resendTimer)
      resendTimer = null
    }
  }, 1000)
}

const benefits = [
  'Exclusive discounts for all bookings',
  'Full access all discounted prices',
  'Dedicated wed-ordinator for your event',
  'Custom event planner for your event'
]

const onSubmit = async () => {
  error.value = ''
  fieldErrors.value = {}
  if (!isExactTenDigitMobile(form.phone)) {
    fieldErrors.value = { phone: ['Enter exactly 10 digits with no +91, spaces, or punctuation.'] }
    error.value = 'Please correct the highlighted fields.'
    return
  }
  pending.value = true
  try {
    const res = await register({
      name: form.name,
      email: form.email,
      username: form.username || undefined,
      password: form.password,
      password_confirmation: form.password_confirmation,
      phone: form.phone,
      terms: form.terms
    })
    verification.value = res.verification || {}
    if (!res.verification?.sent) {
      error.value = res.message || 'Unable to send a verification email.'
    }
    step.value = 'verify'
    startResendCountdown(res.verification?.resend_after || 60)
  } catch (e: unknown) {
    const err = e as { data?: { message?: string, errors?: Record<string, string[]> } }
    fieldErrors.value = err?.data?.errors || {}
    error.value = err?.data?.message || 'Please correct the highlighted fields.'
  } finally {
    pending.value = false
  }
}

const onVerify = async () => {
  error.value = ''
  pending.value = true
  try {
    await verifyEmail({
      email: form.email,
      otp: otp.value
    })
    closeModal()
    const returnTo = consumeReturnTo()
    await navigateTo(returnTo || '/account')
  } catch (e: unknown) {
    const err = e as { data?: { message?: string, errors?: Record<string, string[]> } }
    error.value = err?.data?.errors?.otp?.[0] || err?.data?.message || 'Invalid or expired code.'
  } finally {
    pending.value = false
  }
}

const onResend = async () => {
  if (resendIn.value > 0) {
    return
  }
  error.value = ''
  pending.value = true
  try {
    const res = await resendEmailVerification(form.email)
    verification.value = res.verification || verification.value
    if (!res.verification?.sent) {
      error.value = res.message || 'Unable to send a verification email.'
      return
    }
    startResendCountdown(res.verification?.resend_after || 60)
  } catch (e: unknown) {
    const err = e as { data?: { message?: string, errors?: Record<string, string[]> } }
    error.value = err?.data?.errors?.email?.[0] || err?.data?.message || 'Unable to send a verification email.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :ui="{ content: 'max-w-[860px] w-[calc(100%-1.5rem)] overflow-hidden rounded-sm p-0' }"
  >
    <template #content>
      <div class="relative max-h-[90vh] overflow-y-auto bg-white px-6 py-8 sm:px-10">
        <button
          type="button"
          class="absolute top-2 right-2 z-20 flex size-9 items-center justify-center rounded-full bg-black/50 text-xl text-white hover:bg-brand-500"
          aria-label="Close registration"
          @click="closeModal"
        >
          ×
        </button>

        <h1 class="m-0 text-center font-['Domine',Georgia,serif] text-[26px] font-bold text-[#333] sm:text-[28px]">
          New Member Registration
        </h1>

        <div class="mt-8 flex flex-col gap-8 md:flex-row md:gap-10">
          <div class="md:w-[42%]">
            <h2 class="m-0 text-lg font-semibold text-[#333]">
              Why to sign up
            </h2>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-6 text-[#555]">
              <li
                v-for="item in benefits"
                :key="item"
              >
                {{ item }}
              </li>
            </ul>
          </div>

          <form
            v-if="step === 'form'"
            class="flex flex-1 flex-col gap-3"
            @submit.prevent="onSubmit"
          >
            <input
              v-model="form.name"
              type="text"
              required
              autocomplete="name"
              placeholder="Name"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
            >
            <p
              v-if="fieldErrors.name"
              class="m-0 text-xs text-red-600"
            >
              {{ fieldErrors.name[0] }}
            </p>

            <input
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="Email ID"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
            >
            <p
              v-if="fieldErrors.email"
              class="m-0 text-xs text-red-600"
            >
              {{ fieldErrors.email[0] }}
            </p>

            <input
              v-model="form.username"
              type="text"
              autocomplete="username"
              placeholder="Username (optional)"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
            >
            <p
              v-if="fieldErrors.username"
              class="m-0 text-xs text-red-600"
            >
              {{ fieldErrors.username[0] }}
            </p>

            <input
              v-model="form.password"
              type="password"
              required
              autocomplete="new-password"
              placeholder="Password"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
            >
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              placeholder="Confirm Password"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
            >
            <p
              v-if="fieldErrors.password"
              class="m-0 text-xs text-red-600"
            >
              {{ fieldErrors.password[0] }}
            </p>

            <input
              v-model="form.phone"
              type="tel"
              required
              autocomplete="tel"
              placeholder="Mobile Number"
              maxlength="10"
              inputmode="numeric"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-sm outline-none focus:border-brand-500"
            >
            <p
              v-if="fieldErrors.phone"
              class="m-0 text-xs text-red-600"
            >
              {{ fieldErrors.phone[0] }}
            </p>

            <label class="mt-1 inline-flex items-start gap-2 text-sm text-[#555]">
              <input
                v-model="form.terms"
                type="checkbox"
                required
                class="mt-1 accent-brand-500"
              >
              <span>
                By signing up, I agree to
                <NuxtLink
                  to="/terms"
                  class="text-brand-500 underline"
                >Balaji Royal Events terms of service</NuxtLink>.
              </span>
            </label>
            <p
              v-if="fieldErrors.terms"
              class="m-0 text-xs text-red-600"
            >
              {{ fieldErrors.terms[0] }}
            </p>

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
              {{ pending ? 'Registering…' : 'Register' }}
            </button>

            <p class="mb-0 text-center text-sm text-[#555]">
              Already have an account?
              <button
                type="button"
                class="font-semibold text-brand-500 hover:underline"
                @click="switchToLogin"
              >
                Login
              </button>
            </p>
          </form>

          <form
            v-else
            class="flex flex-1 flex-col gap-3"
            @submit.prevent="onVerify"
          >
            <p class="m-0 text-sm leading-6 text-[#555]">
              We sent a verification code to
              <strong>{{ verification.masked_email || form.email }}</strong>.
              Enter the 6-digit code from your email.
            </p>
            <input
              v-model="otp"
              type="text"
              inputmode="numeric"
              maxlength="6"
              required
              autocomplete="one-time-code"
              placeholder="Enter code"
              class="w-full rounded-sm border border-[#ddd] px-3 py-2.5 text-center text-lg tracking-[0.4em] outline-none focus:border-brand-500"
            >
            <button
              type="submit"
              class="mt-1 rounded-sm border border-brand-500 bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-60"
              :disabled="pending"
            >
              {{ pending ? 'Verifying…' : 'Verify email' }}
            </button>
            <button
              type="button"
              class="text-sm text-brand-500 hover:underline disabled:text-[#999]"
              :disabled="pending || resendIn > 0"
              @click="onResend"
            >
              {{ resendIn > 0 ? `Resend in ${resendIn}s` : 'Resend verification email' }}
            </button>
            <p
              v-if="error"
              class="m-0 text-sm text-red-600"
              role="alert"
            >
              {{ error }}
            </p>
          </form>
        </div>
      </div>
    </template>
  </UModal>
</template>
