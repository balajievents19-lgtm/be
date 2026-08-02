<script setup lang="ts">
import { useServices } from '~/composables/useServices'

const { data: services } = await useServices()
</script>

<template>
  <div class="absolute left-0 top-full z-30 w-[435px] bg-white pb-[15px] pt-2 shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
    <div class="grid grid-cols-2">
      <NuxtLink
        v-for="service in services"
        :key="service.id"
        :to="`/services/${service.slug}`"
        class="px-5 pb-[5px] pt-[11px] text-sm leading-[18px] text-[#202020] transition-colors hover:text-brand-500"
      >
        {{ service.name }}
      </NuxtLink>
    </div>
  </div>
</template>
