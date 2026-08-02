<script setup lang="ts">
const services = [
  'Caterers',
  'Mehndi',
  'Decor & Florists',
  'Cakes',
  'Wedding Planner',
  'Gifts and Flowers',
  'Make-up and Hair',
  'Entertainment',
  'Photographers/ Videographers',
  'DJ',
  'Wedding Cards'
]
</script>

<template>
  <div class="absolute left-0 top-full z-30 w-[435px] bg-white pb-[15px] pt-2 shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
    <div class="grid grid-cols-2">
      <NuxtLink
        v-for="service in services"
        :key="service"
        to="/services"
        class="px-5 pb-[5px] pt-[11px] text-sm leading-[18px] text-[#202020] transition-colors hover:text-brand-500"
      >
        {{ service }}
      </NuxtLink>
    </div>
  </div>
</template>
