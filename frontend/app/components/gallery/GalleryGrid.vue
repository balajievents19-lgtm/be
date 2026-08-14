<script setup lang="ts">
import { computed, ref } from 'vue'
import Lightbox from '~/components/shared/Lightbox.vue'
import { galleryItemAlt, galleryItemSrc } from '~/types/gallery'

const { data: items, pending, failed } = useGallery()

const images = computed(() => (items.value ?? []).map(galleryItemSrc).filter(Boolean))
const isLightboxOpen = ref(false)
const activeIndex = ref(0)

const activeAlt = computed(() => {
  const item = items.value?.[activeIndex.value]
  return item ? galleryItemAlt(item) : undefined
})

const openLightbox = (index: number) => {
  activeIndex.value = index
  isLightboxOpen.value = true
}
</script>

<template>
  <section
    class="gallery-section overflow-hidden py-[50px]"
    aria-label="Event gallery"
  >
    <p
      v-if="pending"
      class="mt-[50px] pb-10 text-center text-sm text-[#666]"
      role="status"
    >
      Loading gallery…
    </p>

    <p
      v-else-if="failed"
      class="mt-[50px] pb-10 text-center text-sm text-[#666]"
      role="alert"
    >
      Unable to load gallery. Please try again later.
    </p>

    <p
      v-else-if="!items?.length"
      class="mt-[50px] pb-10 text-center text-sm text-[#666]"
    >
      No gallery images available right now.
    </p>

    <div
      v-else
      class="gallery-row mt-[50px] -mx-[2px] flex flex-wrap overflow-hidden"
    >
      <button
        v-for="(item, index) in items"
        :key="item.id"
        type="button"
        class="gallery-box group relative w-1/2 bg-[#e1e8ed] p-[2px] min-[768px]:w-1/5"
        :aria-label="`Open ${galleryItemAlt(item)}`"
        @click="openLightbox(index)"
      >
        <img
          :src="galleryItemSrc(item)"
          :alt="galleryItemAlt(item)"
          class="block h-auto w-full object-cover"
          width="400"
          height="300"
          loading="lazy"
          decoding="async"
        >

        <span
          class="pointer-events-none absolute inset-0 bg-[rgba(0,0,0,0.5)] opacity-0 transition-opacity duration-500 ease-in-out group-hover:opacity-100 group-focus-visible:opacity-100"
          aria-hidden="true"
        />

        <span
          class="icon icon-search pointer-events-none absolute top-1/2 left-1/2 z-[99] mt-[-20px] ml-[-20px] w-10 scale-0 text-center text-[40px] leading-10 text-white transition-transform duration-500 ease-in-out group-hover:scale-100 group-focus-visible:scale-100"
          aria-hidden="true"
        />
      </button>
    </div>

    <Lightbox
      v-model:open="isLightboxOpen"
      v-model:index="activeIndex"
      :images="images"
      :alt="activeAlt"
    />
  </section>
</template>
