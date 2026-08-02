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
      ...(form.eventType ? { event_type: form.eventType } : {}),
      ...(form.location ? { location: form.location } : {}),
      ...(form.date ? { date: form.date } : {})
    }
  })
}
</script>

<template>
  <form
    class="mx-auto mb-5 mt-[30px] w-full max-w-[570px] rounded-[3px] bg-white/80 px-0 py-[23px] min-[992px]:mt-[35px] min-[992px]:px-[30px] min-[992px]:pt-[30px] min-[1400px]:mt-[67px]"
    @submit.prevent="search"
  >
    <div class="relative mb-[10px]">
      <span class="icon icon-grid-view pointer-events-none absolute left-0 top-0 z-10 flex h-[50px] w-[37px] items-center justify-center text-lg text-[#464e7b]" />
      <UInput
        v-model="form.eventType"
        placeholder="Event Type"
        aria-label="Event type"
        :ui="{ base: 'h-[50px] rounded border border-[#b8b8b8] bg-white py-[15px] pl-[38px] pr-[10px] text-base text-[#333]' }"
      />
    </div>

    <div class="flex flex-col gap-[10px] min-[992px]:flex-row min-[992px]:gap-0">
      <div class="relative min-[992px]:mr-[1.17%] min-[992px]:w-[67.64%]">
        <span class="icon icon-location-1 pointer-events-none absolute left-0 top-0 z-10 flex h-[50px] w-[37px] items-center justify-center text-lg text-[#464e7b]" />
        <UInput
          v-model="form.location"
          placeholder="Event Location"
          aria-label="Event location"
          :ui="{ base: 'h-[50px] rounded border border-[#b8b8b8] bg-white py-[15px] pl-[38px] pr-[10px] text-base text-[#333]' }"
        />
      </div>

      <div class="relative min-[992px]:w-[30.39%]">
        <span class="icon icon-calander-month pointer-events-none absolute left-0 top-0 z-10 flex h-[50px] w-[37px] items-center justify-center text-lg text-[#464e7b]" />
        <UInput
          v-model="form.date"
          type="date"
          aria-label="Select date"
          :ui="{ base: 'h-[50px] rounded border border-[#b8b8b8] bg-white py-[15px] pl-[38px] pr-[10px] text-base text-[#333]' }"
        />
      </div>
    </div>

    <UButton
      type="submit"
      block
      color="neutral"
      class="mb-[15px] mt-[10px] h-[41px] rounded-[3px] bg-[#f15b22] text-base font-normal text-white hover:bg-[#e7480b]"
    >
      Search Now
    </UButton>

    <p class="m-0 text-center text-sm leading-[21px] text-[#5e5d5d]">
      Create the Perfect Event
    </p>
  </form>
</template>
