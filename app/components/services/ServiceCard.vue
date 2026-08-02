<script setup lang="ts">
import type { Service } from '~/types/service'
import { serviceIconClass } from '~/composables/useServices'

const props = defineProps<{
  item: Service
}>()

const iconClass = computed(() => serviceIconClass(props.item))
</script>

<template>
  <NuxtLink
    :id="item.slug"
    :to="`/services/${item.slug}`"
    class="group block scroll-mt-[140px] bg-white pt-[27px] pb-2.5 text-center text-[#333333] transition-colors duration-300 hover:bg-[#f15b25] hover:text-white focus-visible:bg-[#f15b25] focus-visible:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 max-[991px]:pt-[15px]"
    :aria-label="`View ${item.name} services`"
  >
    <img
      v-if="item.featured_image"
      :src="item.featured_image"
      :alt="item.name"
      class="mx-auto mb-1 h-[68px] w-[68px] object-contain max-[991px]:h-[50px] max-[991px]:w-[50px]"
      loading="lazy"
      decoding="async"
    >
    <span
      v-else
      :class="[
        'icon',
        iconClass,
        'mx-auto inline-block h-[68px] w-full text-center text-[55px] leading-none transition-colors max-[991px]:h-[50px] max-[991px]:text-[40px]'
      ]"
      aria-hidden="true"
    />
    <span
      class="mt-1 inline-block min-h-12 px-[5px] text-center text-base leading-6 max-[991px]:text-sm"
    >
      {{ item.name }}
    </span>
  </NuxtLink>
</template>
