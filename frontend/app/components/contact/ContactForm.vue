<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'

const route = useRoute()
const { submitContact } = usePublicForms()
const { customer, loaded, openLogin, openRegister } = useCustomerAuth()

const packageName = computed(() => {
  const value = route.query.package
  if (typeof value === 'string' && value.trim()) {
    return value.trim()
  }
  return ''
})

const serviceName = computed(() => {
  const value = route.query.service
  if (typeof value === 'string' && value.trim()) {
    return value.trim()
  }
  return ''
})

const enquiryContext = computed(() => {
  if (packageName.value) {
    return {
      kind: 'package' as const,
      label: packageName.value,
      subject: `Package Enquiry: ${packageName.value}`,
      interested: packageName.value,
      source: 'package_inquiry' as const
    }
  }
  if (serviceName.value) {
    return {
      kind: 'service' as const,
      label: serviceName.value,
      subject: `Service inquiry: ${serviceName.value}`,
      interested: serviceName.value,
      source: 'service_inquiry' as const
    }
  }
  return null
})

const form = reactive({
  name: '',
  mobile: '',
  email: '',
  subject: '',
  message: '',
  website: ''
})

const errors = reactive({
  name: '',
  mobile: '',
  email: '',
  subject: '',
  message: ''
})

const submitted = ref(false)
const submitting = ref(false)
const apiError = ref('')

const applyEnquiryContext = () => {
  if (!enquiryContext.value) {
    return
  }
  form.subject = enquiryContext.value.subject
}

watch(enquiryContext, applyEnquiryContext, { immediate: true })

watch([customer, loaded], () => {
  if (!customer.value) {
    return
  }
  form.name = customer.value.name || form.name
  form.email = customer.value.email || form.email
  form.mobile = customer.value.phone || form.mobile
}, { immediate: true })

const identityLocked = computed(() => Boolean(customer.value))

const validate = () => {
  errors.name = form.name.trim() ? '' : 'Name cannot be blank.'
  errors.mobile = isExactTenDigitMobile(form.mobile.trim()) ? '' : 'Enter exactly 10 digits with no +91, spaces, or punctuation.'
  errors.email = !form.email.trim()
    ? 'Email address is required.'
    : (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim()) ? '' : 'Enter a valid email address.')
  errors.subject = form.subject.trim() ? '' : 'Subject cannot be blank.'
  errors.message = form.message.trim() ? '' : 'Message cannot be blank.'
  return !errors.name && !errors.mobile && !errors.email && !errors.subject && !errors.message
}

const onSubmit = async (event: Event) => {
  event.preventDefault()
  submitted.value = false
  apiError.value = ''

  if (form.website.trim()) {
    return
  }

  if (!customer.value) {
    apiError.value = 'Login Required. Please login first to continue.'
    return
  }

  if (!(customer.value.verified_for_enquiry || customer.value.email_verified)) {
    apiError.value = 'Please verify your account before submitting an enquiry.'
    return
  }

  if (!validate()) {
    return
  }

  submitting.value = true
  try {
    await submitContact({
      name: customer.value.name || form.name.trim(),
      mobile: customer.value.phone || form.mobile.trim(),
      email: customer.value.email || form.email.trim(),
      subject: form.subject.trim(),
      message: form.message.trim(),
      service_interested: enquiryContext.value?.interested ?? null,
      source: enquiryContext.value?.source ?? 'contact_page',
      website: ''
    })
    submitted.value = true
    form.name = ''
    form.mobile = ''
    form.email = ''
    form.subject = enquiryContext.value?.subject ?? ''
    form.message = ''
    form.website = ''
  } catch (error: unknown) {
    const err = error as { data?: { message?: string }, message?: string }
    apiError.value = err?.data?.message || err?.message || 'Unable to send your message. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section
    class="contackForm py-20"
    aria-labelledby="contact-form-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <h2
        id="contact-form-heading"
        class="m-0 mb-6 text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
      >
        Contact Form
      </h2>

      <p
        v-if="enquiryContext?.kind === 'package'"
        class="mb-6 rounded-[3px] border border-solid border-[#e6e6e6] bg-[#fafafa] px-4 py-3 text-sm text-[#555]"
        role="status"
      >
        Package:
        <strong class="font-semibold text-[#333]">{{ enquiryContext.label }}</strong>
      </p>
      <p
        v-else-if="enquiryContext?.kind === 'service'"
        class="mb-6 rounded-[3px] border border-solid border-[#e6e6e6] bg-[#fafafa] px-4 py-3 text-sm text-[#555]"
        role="status"
      >
        Service:
        <strong class="font-semibold text-[#333]">{{ enquiryContext.label }}</strong>
      </p>
      <p
        v-if="loaded && !customer"
        class="mb-6 text-sm text-[#555]"
        role="status"
      >
        Login Required. Please login first to continue.
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
        v-else-if="loaded && customer && !(customer.verified_for_enquiry || customer.email_verified)"
        class="mb-6 text-sm text-[#555]"
        role="status"
      >
        Verification required. Please verify your account before submitting an enquiry.
      </p>

      <form
        class="-mx-[15px] flex flex-wrap"
        novalidate
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

        <div class="w-full px-[15px] md:w-1/2">
          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-name"
            >
              Your Name <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-name"
              v-model="form.name"
              type="text"
              name="name"
              autocomplete="name"
              :readonly="identityLocked"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none read-only:bg-[#fafafa]"
            >
            <p
              v-if="errors.name"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.name }}
            </p>
          </div>

          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-mobile"
            >
              Phone <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-mobile"
              v-model="form.mobile"
              type="tel"
              name="mobile"
              autocomplete="tel"
              :readonly="identityLocked"
              maxlength="10"
              inputmode="numeric"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none read-only:bg-[#fafafa]"
            >
            <p
              v-if="errors.mobile"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.mobile }}
            </p>
          </div>

          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-email"
            >
              Your Email <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-email"
              v-model="form.email"
              type="text"
              name="email"
              autocomplete="email"
              :readonly="identityLocked"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none read-only:bg-[#fafafa]"
            >
            <p
              v-if="errors.email"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.email }}
            </p>
          </div>

          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-subject"
            >
              Subject <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-subject"
              v-model="form.subject"
              type="text"
              name="subject"
              :readonly="Boolean(enquiryContext)"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none read-only:bg-[#fafafa]"
            >
            <p
              v-if="errors.subject"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.subject }}
            </p>
          </div>
        </div>

        <div class="w-full px-[15px] md:w-1/2">
          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-message"
            >
              Your Message <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <textarea
              id="contact-message"
              v-model="form.message"
              name="message"
              class="box-border h-[218px] w-full resize-none border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none"
            />
            <p
              v-if="errors.message"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.message }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="float-right w-[170px] cursor-pointer rounded-[3px] border border-solid border-brand-500 bg-brand-500 py-[14px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:opacity-60"
          >
            Submit
          </button>

          <p
            v-if="apiError"
            class="clear-both mt-4 text-sm text-[#ff0000]"
            role="alert"
          >
            {{ apiError }}
          </p>

          <p
            v-if="submitted"
            class="clear-both mt-4 text-sm text-[#6a6767]"
            role="status"
          >
            Thank you. Your message has been recorded.
          </p>
        </div>
      </form>
    </UContainer>
  </section>
</template>
