<script setup lang="ts">
import { computed, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Keyboard, Navigation } from 'swiper/modules'
import type { Service } from '~/types/service'
import { galleryItemSrc } from '~/types/gallery'

import 'swiper/css'
import 'swiper/css/navigation'

const props = defineProps<{
  service: Service
}>()

const modules = [A11y, Keyboard, Navigation]
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

const relatedItems = computed(() => props.service.related_gallery?.filter(item => galleryItemSrc(item)) ?? [])

const gallery = computed(() => {
  if (relatedItems.value.length) {
    return relatedItems.value.map(item => galleryItemSrc(item))
  }
  const images = props.service.gallery_images?.filter(Boolean) ?? []
  if (images.length) {
    return images
  }
  return [props.service.featured_image, props.service.banner_image].filter(Boolean) as string[]
})

const viewAllTo = computed(() => {
  const firstCategory = relatedItems.value.find(item => item.category?.slug)?.category?.slug
  return firstCategory ? `/gallery/${firstCategory}` : '/gallery'
})

const onSwiper = (swiper: { slideNext: () => void, slidePrev: () => void }) => {
  swiperRef.value = swiper
}
</script>

<template>
  <div
    v-if="gallery.length"
    class="event-galler mt-[30px] bg-white pt-4"
  >
    <h2 class="mb-[15px] flex items-baseline justify-between gap-3 border-b border-solid border-[#e0e0e0] px-5 pb-1.5 text-2xl leading-9 font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]">
      <span>Gallery</span>
      <NuxtLink
        v-if="relatedItems.length"
        :to="viewAllTo"
        class="text-sm font-normal text-brand-500 no-underline hover:text-brand-600"
      >
        View All Work →
      </NuxtLink>
    </h2>
    <div class="event-gallerSlider relative px-12 py-10 max-[767px]:px-[50px] min-[768px]:px-24">
      <Swiper
        :modules="modules"
        :slides-per-view="1"
        :space-between="16"
        :breakpoints="{ 768: { slidesPerView: 2 } }"
        :keyboard="{ enabled: true }"
        class="w-full"
        @swiper="onSwiper"
      >
        <SwiperSlide
          v-for="(src, index) in gallery"
          :key="relatedItems[index]?.id ?? `${src}-${index}`"
        >
          <img
            :src="src"
            :alt="`${service.name} gallery ${index + 1}`"
            class="block h-auto w-full"
            width="800"
            height="500"
            loading="lazy"
            decoding="async"
          >
        </SwiperSlide>
      </Swiper>

      <button
        type="button"
        class="absolute top-1/2 left-[15px] z-10 mt-[-17px] text-[#333] min-[768px]:left-[39px]"
        aria-label="Previous gallery image"
        @click="swiperRef?.slidePrev()"
      >
        <span
          class="icon icon-arrow-left text-2xl"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        class="absolute top-1/2 right-[15px] z-10 mt-[-17px] text-[#333] min-[768px]:right-[39px]"
        aria-label="Next gallery image"
        @click="swiperRef?.slideNext()"
      >
        <span
          class="icon icon-arrow-right text-2xl"
          aria-hidden="true"
        />
      </button>
    </div>
  </div>
</template>
