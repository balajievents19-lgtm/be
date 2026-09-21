<script setup lang="ts">
import type { Service } from '~/types/service'
import { resolveServiceIcon } from '~/utils/serviceIcon'

const props = defineProps<{
  item: Service
}>()

const iconClass = computed(() => resolveServiceIcon(props.item.icon, props.item.slug))
</script>

<template>
  <NuxtLink
    :id="item.slug"
    :to="`/services/${item.slug}`"
    class="group flex h-full min-h-[160px] flex-col items-center justify-start rounded-[4px] border border-solid border-[#ececec] bg-white px-2 pt-[27px] pb-3 text-center text-[#333333] shadow-[0_4px_14px_rgba(16,15,15,0.06)] transition-all duration-300 hover:border-brand-500 hover:bg-brand-500 hover:text-white hover:shadow-[0_8px_22px_rgba(241,91,34,0.28)] focus-visible:border-brand-500 focus-visible:bg-brand-500 focus-visible:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 max-[991px]:min-h-[140px] max-[991px]:pt-[15px]"
    :aria-label="`View ${item.name} services`"
  >
    <span
      class="flex h-[68px] w-full items-center justify-center text-brand-500 transition-colors group-hover:text-white group-focus-visible:text-white max-[991px]:h-[50px]"
      aria-hidden="true"
    >
      <i
        :class="[
          'icon',
          iconClass,
          'inline-block text-center text-[55px] leading-none max-[991px]:text-[40px]'
        ]"
      />
    </span>
    <span
      class="mt-2 inline-block min-h-12 px-[5px] text-center text-base leading-6 font-medium max-[991px]:min-h-10 max-[991px]:text-sm"
    >
      {{ item.name }}
    </span>
  </NuxtLink>
</template>
