<script setup lang="ts">
import { reactive, ref } from 'vue'
import FormsAppDatePicker from '~/components/forms/AppDatePicker.vue'
import FormsAppEventTypeSelect from '~/components/forms/AppEventTypeSelect.vue'
import FormsAppLocationPicker from '~/components/forms/AppLocationPicker.vue'

interface SearchForm {
  name: string
  mobile: string
  eventTypeId: string
  location: string
  date: string
  budget: string
  website: string
}

const { submitContact } = usePublicForms()
const { data: eventTypes } = useEventTypes()

const form = reactive<SearchForm>({
  name: '',
  mobile: '',
  eventTypeId: '',
  location: '',
  date: '',
  budget: '',
  website: ''
})

const submitting = ref(false)
const submitted = ref(false)
const apiError = ref('')

const search = async () => {
  submitted.value = false
  apiError.value = ''

  if (form.website.trim()) {
    return
  }

  if (!form.eventTypeId.trim() || !form.location.trim() || !form.date.trim()) {
    apiError.value = 'Event Type, Event Location, and Event Date are required.'
    return
  }

  if (!form.name.trim() || !form.mobile.trim()) {
    apiError.value = 'Name and phone are required so we can contact you.'
    return
  }

  const selected = eventTypes.value.find(item => String(item.id) === form.eventTypeId)
  if (!selected) {
    apiError.value = 'Please select a valid event type.'
    return
  }

  submitting.value = true
  try {
    await submitContact({
      name: form.name.trim(),
      mobile: form.mobile.trim(),
      subject: `Slider inquiry: ${selected.name}`,
      message: [
        `Event Type: ${selected.name}`,
        `Event Location: ${form.location.trim()}`,
        `Event Date: ${form.date.trim()}`,
        form.budget.trim() ? `Budget: ${form.budget.trim()}` : null
      ].filter(Boolean).join('\n'),
      event_type_id: selected.id,
      service_interested: selected.name,
      event_date: form.date.trim(),
      event_location: form.location.trim(),
      budget: form.budget.trim() || null,
      source: 'slider',
      website: ''
    })

    submitted.value = true
    form.name = ''
    form.mobile = ''
    form.eventTypeId = ''
    form.location = ''
    form.date = ''
    form.budget = ''
    form.website = ''
  } catch (error: unknown) {
    const err = error as { data?: { message?: string, errors?: Record<string, string[]> }, message?: string }
    const firstValidation = err?.data?.errors
      ? Object.values(err.data.errors).flat()[0]
      : null
    apiError.value = firstValidation || err?.data?.message || err?.message || 'Unable to send your inquiry. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <form
    class="banner-search relative mx-auto mb-0 w-full max-w-[570px] rounded-[3px] bg-[rgba(255,255,255,0.8)] px-[30px] pt-[30px] pb-[23px] max-[991px]:mt-6 min-[992px]:mt-8 min-[1200px]:mt-10 lg:max-w-[960px]"
    aria-label="Event inquiry"
    @submit.prevent="search"
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

    <div class="mb-2.5 grid grid-cols-1 gap-x-[1.17%] gap-y-2.5 md:grid-cols-2 lg:grid-cols-3">
      <div class="relative min-w-0 w-full">
        <span
          class="icon icon-user pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <input
          v-model="form.name"
          type="text"
          name="name"
          placeholder="Your Name"
          aria-label="Your name"
          autocomplete="name"
          class="box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
        >
      </div>

      <div class="relative min-w-0 w-full">
        <span
          class="icon icon-phone pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <input
          v-model="form.mobile"
          type="tel"
          name="mobile"
          placeholder="Phone Number"
          aria-label="Phone number"
          autocomplete="tel"
          class="box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
        >
      </div>

      <div class="min-w-0 w-full">
        <FormsAppEventTypeSelect
          v-model="form.eventTypeId"
          required
        />
      </div>

      <div class="relative min-w-0 w-full">
        <FormsAppLocationPicker
          v-model="form.location"
          required
        />
      </div>

      <div class="relative min-w-0 w-full">
        <FormsAppDatePicker
          v-model="form.date"
          required
        />
      </div>

      <div class="relative min-w-0 w-full">
        <span
          class="icon icon-dollar pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <input
          v-model="form.budget"
          type="text"
          name="budget"
          placeholder="Budget (optional)"
          aria-label="Budget optional"
          class="box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
        >
      </div>
    </div>

    <p
      v-if="apiError"
      class="mb-2.5 text-center text-sm text-red-600"
      role="alert"
    >
      {{ apiError }}
    </p>

    <p
      v-if="submitted"
      class="mb-2.5 text-center text-sm text-green-700"
      role="status"
    >
      Thank you! Our team will contact you shortly.
    </p>

    <div class="mb-[15px] w-full">
      <button
        type="submit"
        class="box-border w-full cursor-pointer rounded-[3px] border border-solid border-brand-500 bg-brand-500 py-[14px] text-center text-lg font-normal leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:cursor-wait disabled:opacity-70"
        :disabled="submitting"
      >
        {{ submitting ? 'Sending…' : 'Search Now' }}
      </button>
    </div>

    <p class="m-0 text-center text-sm leading-[21px] text-[#5e5d5d]">
      Create the Perfect Event
    </p>
  </form>
</template>
