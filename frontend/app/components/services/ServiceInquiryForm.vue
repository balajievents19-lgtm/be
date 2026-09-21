<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import FormsAppDatePicker from '~/components/forms/AppDatePicker.vue'

const props = defineProps<{
  serviceTitle: string
}>()

const { submitContact } = usePublicForms()
const { customer, loaded, openLogin, openRegister } = useCustomerAuth()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  date: '',
  message: '',
  website: ''
})

const sent = ref(false)
const submitting = ref(false)
const apiError = ref('')

const applyCustomer = () => {
  const current = customer.value
  if (!current) {
    form.name = ''
    form.email = ''
    form.phone = ''
    return
  }
  form.name = current.name || ''
  form.email = current.email || ''
  form.phone = current.phone || ''
}

watch([customer, loaded], applyCustomer, { immediate: true })

const canSubmitInquiry = computed(() => {
  return Boolean(
    customer.value
    && customer.value.email_verified
    && isExactTenDigitMobile(customer.value.phone || '')
  )
})

const onSubmit = async (event: Event) => {
  event.preventDefault()
  sent.value = false
  apiError.value = ''

  if (form.website.trim()) {
    return
  }

  if (!customer.value) {
    apiError.value = 'Sign in with a verified account to send an inquiry.'
    openLogin()
    return
  }

  if (!customer.value.email_verified) {
    apiError.value = 'Verify your email address before sending an inquiry.'
    return
  }

  if (!isExactTenDigitMobile(customer.value.phone || '')) {
    apiError.value = 'Add a registered 10-digit mobile number on your account before sending an inquiry.'
    return
  }

  submitting.value = true
  try {
    await submitContact({
      name: customer.value.name || form.name.trim(),
      mobile: customer.value.phone || '',
      email: customer.value.email,
      subject: `Service inquiry: ${props.serviceTitle}`,
      message: form.message.trim() || `Inquiry for ${props.serviceTitle}`,
      service_interested: props.serviceTitle,
      event_date: form.date.trim() || null,
      source: 'service_inquiry',
      website: ''
    })
    sent.value = true
    form.date = ''
    form.message = ''
    form.website = ''
  } catch (error: unknown) {
    const err = error as { data?: { message?: string }, message?: string }
    apiError.value = err?.data?.message || err?.message || 'Unable to send inquiry. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="filter-block booking-form bg-white pb-5">
    <div class="title border-b border-solid border-[#e0e0e0] px-5 pb-0">
      <h2 class="m-0 pb-2.5 font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-normal leading-[26px] text-[#333]">
        Inquiry — {{ serviceTitle }}
      </h2>
    </div>

    <form
      class="form-filde block px-5 pt-[5px]"
      @submit="onSubmit"
    >
      <input
        v-model="form.website"
        type="text"
        name="website"
        tabindex="-1"
        autocomplete="off"
        class="absolute left-[-10000px] h-px w-px overflow-hidden"
        aria-hidden="true"
      >

      <p
        v-if="loaded && !customer"
        class="mb-2.5 text-[13px] leading-[20px] text-[#555]"
        role="status"
      >
        Sign in with a verified account to send this inquiry.
        <button
          type="button"
          class="text-brand-500 underline"
          @click="openLogin()"
        >
          Login
        </button>
        or
        <button
          type="button"
          class="text-brand-500 underline"
          @click="openRegister()"
        >
          Register
        </button>
      </p>
      <p
        v-else-if="customer && !customer.email_verified"
        class="mb-2.5 text-[13px] leading-[20px] text-[#555]"
        role="status"
      >
        Verify your email before sending an inquiry.
      </p>
      <p
        v-else-if="customer && !isExactTenDigitMobile(customer.phone || '')"
        class="mb-2.5 text-[13px] leading-[20px] text-[#555]"
        role="status"
      >
        Add a 10-digit mobile number on your account before sending an inquiry.
      </p>

      <div class="input-box mb-2.5 block w-full">
        <label
          class="sr-only"
          for="inquiry-name"
        >Name</label>
        <input
          id="inquiry-name"
          v-model="form.name"
          type="text"
          placeholder="Your Name"
          required
          readonly
          class="box-border h-[38px] w-full rounded-[3px] border border-solid border-[#cccccc] bg-[#fafafa] px-2.5 py-[5px] text-[13px] leading-[26px] text-[#333] italic outline-none"
        >
      </div>
      <div class="input-box mb-2.5 block w-full">
        <label
          class="sr-only"
          for="inquiry-email"
        >Email</label>
        <input
          id="inquiry-email"
          v-model="form.email"
          type="email"
          placeholder="Email"
          readonly
          class="box-border h-[38px] w-full rounded-[3px] border border-solid border-[#cccccc] bg-[#fafafa] px-2.5 py-[5px] text-[13px] leading-[26px] text-[#333] italic outline-none"
        >
      </div>
      <div class="input-box mb-2.5 block w-full">
        <label
          class="sr-only"
          for="inquiry-phone"
        >Phone</label>
        <input
          id="inquiry-phone"
          v-model="form.phone"
          type="text"
          placeholder="Phone"
          required
          readonly
          maxlength="10"
          inputmode="numeric"
          class="box-border h-[38px] w-full rounded-[3px] border border-solid border-[#cccccc] bg-[#fafafa] px-2.5 py-[5px] text-[13px] leading-[26px] text-[#333] italic outline-none"
        >
      </div>
      <div class="input-box relative mb-2.5 block w-full">
        <label
          class="sr-only"
          for="inquiry-date"
        >Event Date</label>
        <FormsAppDatePicker
          id="inquiry-date"
          v-model="form.date"
          compact
          placeholder="Select Date"
        />
      </div>
      <div class="input-box mb-2.5 block w-full">
        <label
          class="sr-only"
          for="inquiry-message"
        >Message</label>
        <input
          id="inquiry-message"
          v-model="form.message"
          type="text"
          placeholder="Message"
          class="box-border h-[38px] w-full rounded-[3px] border border-solid border-[#cccccc] px-2.5 py-[5px] text-[13px] leading-[26px] text-[#333] italic outline-none"
        >
      </div>
      <div class="submit-box mt-0.5 block">
        <button
          type="submit"
          :disabled="submitting || (loaded && !canSubmitInquiry)"
          class="h-[38px] w-full cursor-pointer rounded-[3px] border border-solid border-brand-500 bg-brand-500 text-sm text-white transition-colors hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:opacity-60"
        >
          Send Inquiry
        </button>
      </div>
      <p
        v-if="apiError"
        class="mt-2 text-xs text-[#ff0000]"
        role="alert"
      >
        {{ apiError }}
      </p>
      <p
        v-if="sent"
        class="mt-2 text-xs text-[#6a6767]"
        role="status"
      >
        Inquiry recorded.
      </p>
    </form>
  </div>
</template>
