<script setup lang="ts">
import { reactive } from 'vue'
import { navigateTo } from '#imports'

interface SearchForm {
  eventType: string
  location: string
  date: string
}

const form = reactive<SearchForm>({
  eventType: '',
  location: '',
  date: ''
})

const search = async () => {
  await navigateTo({
    path: '/services',
    query: {
      ...(form.eventType.trim() ? { event_type: form.eventType.trim() } : {}),
      ...(form.location.trim() ? { location: form.location.trim() } : {}),
      ...(form.date ? { date: form.date } : {})
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
    <div class="relative mb-2.5 inline-block w-full">
      <span
        class="icon icon-grid-view pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
        aria-hidden="true"
      />
      <UInput
        v-model="form.eventType"
        name="event_type"
        placeholder="Event Type"
        aria-label="Event type"
        autocomplete="off"
        :ui="{
          base: 'h-[50px] w-full rounded border border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] focus-visible:ring-1 focus-visible:ring-[#f15b22]'
        }"
      />
    </div>

    <div class="mb-2.5 flex w-full flex-col max-[767px]:gap-2.5 min-[768px]:flex-row min-[768px]:gap-0">
      <div class="relative mb-0 w-full max-[767px]:mb-2.5 min-[768px]:mr-[1.17%] min-[768px]:w-[67.64%]">
        <span
          class="icon icon-location-1 pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <UInput
          v-model="form.location"
          name="location"
          placeholder="Event Location"
          aria-label="Event location"
          autocomplete="address-level2"
          :ui="{
            base: 'h-[50px] w-full rounded border border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] focus-visible:ring-1 focus-visible:ring-[#f15b22]'
          }"
        />
      </div>

      <div class="relative w-full min-[768px]:w-[30.39%]">
        <span
          class="icon icon-calander-month pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
          aria-hidden="true"
        />
        <UInput
          v-model="form.date"
          name="date"
          type="date"
          placeholder="Select Date"
          aria-label="Select date"
          :ui="{
            base: 'h-[50px] w-full rounded border border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] focus-visible:ring-1 focus-visible:ring-[#f15b22]'
          }"
        />
      </div>
    </div>

    <div class="mb-[15px] w-full">
      <UButton
        type="submit"
        block
        color="neutral"
        class="h-auto w-full rounded-[3px] border border-[#f15b22] bg-[#f15b22] py-[14px] text-lg font-normal leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-[#e7480b] hover:bg-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
      >
        Search Now
      </UButton>
    </div>

    <p class="m-0 text-center text-sm leading-[21px] text-[#5e5d5d]">
      Create the Perfect Event
    </p>
  </form>
</template>
