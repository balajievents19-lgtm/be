<script setup lang="ts">
import type { StatisticItem } from '~/types/home'

withDefaults(defineProps<{
  items?: StatisticItem[]
}>(), {
  items: () => []
})
</script>

<template>
  <section
    v-if="items.length"
    class="border-b border-[#ead9c4] bg-white py-10"
    aria-label="Highlights"
  >
    <UContainer class="mx-auto max-w-[1170px] px-4">
      <ul class="grid grid-cols-2 gap-6 text-center md:grid-cols-4">
        <li
          v-for="item in items"
          :key="item.id"
        >
          <p class="m-0 font-['Domine',Georgia,serif] text-3xl text-brand-500">
            {{ item.value }}<span v-if="item.suffix">{{ item.suffix }}</span>
          </p>
          <p class="mt-1 text-sm text-[#555]">
            {{ item.label }}
          </p>
        </li>
      </ul>
    </UContainer>
  </section>
</template>
