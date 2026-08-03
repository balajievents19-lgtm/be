<script setup lang="ts">
import { reactive } from 'vue'
import { navigateTo } from '#imports'
import type { Service } from '~/types/service'

interface SearchForm {
  eventType: string
  location: string
  date: string
}

const props = withDefaults(defineProps<{
  services?: Service[]
}>(), {
  services: () => []
})

const form = reactive<SearchForm>({
  eventType: '',
  location: '',
  date: ''
})

const search = async () => {
  const selected = props.services.find(item => item.slug === form.eventType)

  if (selected) {
    await navigateTo({
      path: `/services/${selected.slug}`,
      query: {
        ...(form.location.trim() ? { location: form.location.trim() } : {}),
        ...(form.date.trim() ? { date: form.date.trim() } : {})
      }
    })
    return
  }

  await navigateTo({
    path: '/services',
    query: {
      ...(form.eventType.trim() ? { event_type: form.eventType.trim() } : {}),
      ...(form.location.trim() ? { location: form.location.trim() } : {}),
      ...(form.date.trim() ? { date: form.date.trim() } : {})
    }
  })
}
</script>

<template>
  <form
    class="banner-search mx-auto mb-5 w-full max-w-[570px] rounded-[3px] bg-[rgba(255,255,255,0.8)] px-[30px] pt-[30px] pb-[23px] max-[991px]:mt-[30px] max-[991px]:mb-5 min-[992px]:mt-[30px] min-[1200px]:mt-[35px] min-[1400px]:mt-[67px]"
    aria-label="Search events"
    @submit.prevent="search"
  >
    <!-- Event Type — full width (master .input-box) -->
    <div class="relative mb-2.5 inline-block w-full">
      <span
        class="icon icon-grid-view pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
        aria-hidden="true"
      />
      <select
        v-model="form.eventType"
        name="event_type"
        aria-label="Event type"
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

    <!-- Location 67.64% + Date 30.39% (master .location / .date) -->
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
          type="text"
          name="date"
          placeholder="Select Date"
          aria-label="Select date"
          autocomplete="off"
          class="box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none"
        >
      </div>
    </div>

    <div class="mb-[15px] w-full">
      <button
        type="submit"
        class="box-border w-full cursor-pointer rounded-[3px] border border-solid border-brand-500 bg-brand-500 py-[14px] text-center text-lg font-normal leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
      >
        Search Now
      </button>
    </div>

    <p class="m-0 text-center text-sm leading-[21px] text-[#5e5d5d]">
      Create the Perfect Event
    </p>
  </form>
</template>
