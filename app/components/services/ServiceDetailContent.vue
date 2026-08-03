<script setup lang="ts">
import type { Service } from '~/types/service'

const props = defineProps<{
  service: Service
  relatedNames?: string[]
}>()

const emit = defineEmits<{
  inquire: []
}>()

const descriptionText = computed(() =>
  props.service.short_description
  || ''
)

const fullDescriptionText = computed(() => {
  const html = props.service.full_description
  if (!html) {
    return ''
  }
  return html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
})

const features = computed(() => {
  if (props.relatedNames?.length) {
    return props.relatedNames
  }
  return props.service.name ? [props.service.name] : []
})

const imageSrc = computed(() =>
  props.service.banner_image
  || props.service.featured_image
  || ''
)
</script>

<template>
  <div class="service-right">
    <div class="event-info bg-white pt-4">
      <h2
        class="mb-[15px] block border-b border-solid border-[#e0e0e0] px-5 pb-1.5 text-2xl leading-9 font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
      >
        {{ service.name }}
      </h2>
      <p
        v-if="descriptionText"
        class="m-0 block px-6 pb-5 text-[13px] leading-6 text-[#666666]"
      >
        {{ descriptionText }}
      </p>
      <p
        v-if="fullDescriptionText"
        class="m-0 block px-6 pb-5 text-[13px] leading-6 text-[#666666]"
      >
        {{ fullDescriptionText }}
      </p>
    </div>

    <div class="service-details px-5 pb-5">
      <div
        v-if="imageSrc"
        class="img w-full"
      >
        <img
          :src="imageSrc"
          :alt="service.name"
          class="block h-auto w-full"
          width="800"
          height="500"
          loading="lazy"
          decoding="async"
        >
      </div>
      <h3 class="m-0 mt-5 text-lg font-semibold leading-6 text-[#333333]">
        {{ service.name }} Features
      </h3>
      <ul class="customList m-0 list-none p-0 pt-[15px]">
        <li
          v-for="feature in features"
          :key="feature"
          class="relative inline-block w-full py-0 pr-0 pl-[22px] text-sm leading-7 text-[#333] before:absolute before:top-px before:left-0 before:font-['untitled-font-20'] before:text-xs before:leading-7 before:text-[#8ec24f] before:content-['\7d'] max-[479px]:w-full min-[480px]:w-[49%]"
        >
          {{ feature }}
        </li>
      </ul>

      <div class="mt-6 flex flex-wrap gap-3">
        <NuxtLink
          to="/contact"
          class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
        >
          Inquiry
        </NuxtLink>
        <button
          type="button"
          class="inline-block cursor-pointer rounded-[3px] border border-solid border-brand-500 bg-white px-7 py-[9px] text-center text-lg leading-5 text-brand-500 transition-colors hover:bg-brand-500 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          @click="emit('inquire')"
        >
          Request Booking
        </button>
      </div>
    </div>
  </div>
</template>
