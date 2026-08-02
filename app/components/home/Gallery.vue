<script setup lang="ts">
import { ref } from 'vue'
import Lightbox from '~/components/shared/Lightbox.vue'

const images = [
  '/images/gallery/home3-galleryImg1.jpg',
  '/images/gallery/home3-galleryImg2.jpg',
  '/images/gallery/home3-galleryImg3.jpg',
  '/images/gallery/home3-galleryImg4.jpg',
  '/images/gallery/home3-galleryImg5.jpg',
  '/images/gallery/home3-galleryImg6.jpg',
  '/images/gallery/home3-galleryImg7.jpg',
  '/images/gallery/home3-galleryImg8.jpg',
  '/images/gallery/home3-galleryImg9.jpg',
  '/images/gallery/home3-galleryImg10.jpg'
]

const isLightboxOpen = ref(false)
const activeIndex = ref(0)

const openLightbox = (index: number) => {
  activeIndex.value = index
  isLightboxOpen.value = true
}
</script>

<template>
  <section
    class="overflow-hidden py-[50px]"
    aria-labelledby="gallery-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="w-full text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-[#f15b22]"
            aria-hidden="true"
          />
        </div>

        <div class="relative mx-auto mt-7 mb-[23px] inline-block w-full max-w-[761px]">
          <div
            class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
            aria-hidden="true"
          />
          <h2
            id="gallery-heading"
            class="relative z-[2] m-0 inline-block bg-white px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Gallery
          </h2>
        </div>
      </div>
    </UContainer>

    <div class="mt-[50px] flex flex-wrap overflow-hidden px-0">
      <button
        v-for="(src, index) in images"
        :key="src"
        type="button"
        class="group relative w-1/2 bg-[#e1e8ed] p-[2px] md:w-1/5"
        :aria-label="`Open gallery image ${index + 1}`"
        @click="openLightbox(index)"
      >
        <img
          :src="src"
          :alt="`Gallery image ${index + 1}`"
          class="block h-auto w-full"
          loading="lazy"
        >

        <span
          class="pointer-events-none absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-500 group-hover:opacity-100 group-focus-visible:opacity-100"
          aria-hidden="true"
        />

        <span
          class="icon icon-search pointer-events-none absolute top-1/2 left-1/2 z-[99] w-10 -translate-x-1/2 -translate-y-1/2 scale-0 text-center text-[40px] leading-10 text-white transition-transform duration-500 group-hover:scale-100 group-focus-visible:scale-100"
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
