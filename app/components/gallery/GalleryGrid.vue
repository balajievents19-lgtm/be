<script setup lang="ts">
import { computed, ref } from 'vue'
import Lightbox from '~/components/shared/Lightbox.vue'
import { galleryImages } from '~/data/gallery'

const images = computed(() => galleryImages.map(item => item.src))
const isLightboxOpen = ref(false)
const activeIndex = ref(0)

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
    <div class="gallery-row mt-[50px] -mx-[2px] flex flex-wrap overflow-hidden">
      <button
        v-for="(item, index) in galleryImages"
        :key="item.src"
        type="button"
        class="gallery-box group relative w-1/2 bg-[#e1e8ed] p-[2px] min-[768px]:w-1/5"
        :aria-label="`Open ${item.alt}`"
        @click="openLightbox(index)"
      >
        <img
          :src="item.src"
          :alt="item.alt"
          class="block h-auto w-full object-cover"
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
    />
  </section>
</template>
