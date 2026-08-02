<script setup lang="ts">
import { ref } from 'vue'

const eventType = ref('')
const location = ref('')
const date = ref('')
</script>

<template>
  <div
    class="mx-auto w-full max-w-[560px] rounded bg-white p-6 shadow-2xl"
  >

    <!-- Event Type -->

    <div class="mb-4">

      <div
        class="flex h-14 items-center rounded border border-gray-300 px-4"
      >

        <UIcon
          name="i-lucide-briefcase"
          class="mr-3 h-5 w-5 text-gray-500"
        />

        <input
          v-model="eventType"
          type="text"
          placeholder="Event Type"
          class="w-full border-0 outline-none"
        >

      </div>

    </div>

    <!-- Location + Date -->

    <div class="mb-4 grid grid-cols-2 gap-3">

      <div
        class="flex h-14 items-center rounded border border-gray-300 px-4"
      >

        <UIcon
          name="i-lucide-map-pin"
          class="mr-3 h-5 w-5 text-gray-500"
        />

        <input
          v-model="location"
          placeholder="Event Location"
          class="w-full border-0 outline-none"
        >

      </div>

      <div
        class="flex h-14 items-center rounded border border-gray-300 px-4"
      >

        <UIcon
          name="i-lucide-calendar"
          class="mr-3 h-5 w-5 text-gray-500"
        />

        <input
          v-model="date"
          type="date"
          class="w-full border-0 outline-none"
        >

      </div>

    </div>

    <!-- Button -->

    <button
      class="h-14 w-full bg-[#f85a1f] text-lg font-semibold text-white transition hover:bg-[#df4c15]"
    >
      Search Now
    </button>

    <p class="mt-5 text-center text-gray-500">
      Create the Perfect Event
    </p>

  </div>
</template>