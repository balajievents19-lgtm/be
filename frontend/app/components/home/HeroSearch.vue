<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { Service } from '~/types/service'

interface SearchForm {
  name: string
  mobile: string
  eventType: string
  location: string
  date: string
  budget: string
  website: string
}

const props = withDefaults(defineProps<{
  services?: Service[]
}>(), {
  services: () => []
})

const { submitContact } = usePublicForms()

const form = reactive<SearchForm>({
  name: '',
  mobile: '',
  eventType: '',
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

  if (!form.eventType.trim() || !form.location.trim() || !form.date.trim()) {
    apiError.value = 'Event Type, Event Location, and Event Date are required.'
    return
  }

  if (!form.name.trim() || !form.mobile.trim()) {
    apiError.value = 'Name and phone are required so we can contact you.'
    return
  }

  const selected = props.services.find(item => item.slug === form.eventType)
  const serviceName = selected?.name || form.eventType.trim()

  submitting.value = true
  try {
    await submitContact({
      name: form.name.trim(),
      mobile: form.mobile.trim(),
      subject: `Slider inquiry: ${serviceName}`,
      message: [
        `Event Type: ${serviceName}`,
        `Event Location: ${form.location.trim()}`,
        `Event Date: ${form.date.trim()}`,
        form.budget.trim() ? `Budget: ${form.budget.trim()}` : null
      ].filter(Boolean).join('\n'),
      service_interested: serviceName,
      event_date: form.date.trim(),
      event_location: form.location.trim(),
      budget: form.budget.trim() || null,
      source: 'slider',
      website: ''
    })

    submitted.value = true
    form.name = ''
    form.mobile = ''
    form.eventType = ''
    form.location = ''
    form.date = ''
    form.budget = ''
    form.website = ''
  } catch (error: unknown) {
    const err = error as { data?: { message?: string }, message?: string }
    apiError.value = err?.data?.message || err?.message || 'Unable to send your inquiry. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <form
    class="banner-search mx-auto mb-5 w-full max-w-[570px] rounded-[3px] bg-[rgba(255,255,255,0.8)] px-[30px] pt-[30px] pb-[23px] max-[991px]:mt-[30px] max-[991px]:mb-5 min-[992px]:mt-[30px] min-[1200px]:mt-[35px] min-[1400px]:mt-[67px]"
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

    <div class="relative mb-2.5 inline-block w-full">
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

    <div class="relative mb-2.5 inline-block w-full">
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

    <div class="relative mb-2.5 inline-block w-full">
      <span
        class="icon icon-grid-view pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
        aria-hidden="true"
      />
      <select
        v-model="form.eventType"
        name="event_type"
        aria-label="Event type"
        required
        class="box-border h-[50px] w-full appearance-none rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
      >
        <option value="">
          Event Type
        </option>
        <option
          v-for="service in services"
          :key="service.id"
          :value="service.slug"
        >
          {{ service.name }}
        </option>
      </select>
    </div>

    <div class="mb-2.5 w-full max-[767px]:block min-[768px]:flex min-[768px]:items-start">
      <div class="relative mb-2.5 inline-block w-full max-[767px]:mb-2.5 min-[768px]:mb-0 min-[768px]:mr-[1.17%] min-[768px]:w-[67.64%]">
        <span
          class="icon icon-location-1 pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <input
          v-model="form.location"
          type="text"
          name="location"
          placeholder="Event Location"
          aria-label="Event location"
          autocomplete="address-level2"
          required
          class="box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
        >
      </div>

      <div class="relative inline-block w-full min-[768px]:w-[30.39%]">
        <span
          class="icon icon-calander-month pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <input
          v-model="form.date"
          type="date"
          name="date"
          aria-label="Event date"
          required
          class="box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
        >
      </div>
    </div>

    <div class="relative mb-2.5 inline-block w-full">
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
