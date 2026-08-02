<script setup lang="ts">
import { ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Keyboard, Navigation } from 'swiper/modules'
import type { ServiceDetail } from '~/data/services'

import 'swiper/css'
import 'swiper/css/navigation'

defineProps<{
  service: ServiceDetail
}>()

const modules = [A11y, Keyboard, Navigation]
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

const onSwiper = (swiper: { slideNext: () => void, slidePrev: () => void }) => {
  swiperRef.value = swiper
}
</script>

<template>
  <div class="event-galler mt-[30px] bg-white pt-4">
    <h2 class="mb-[15px] block border-b border-solid border-[#e0e0e0] px-5 pb-1.5 text-2xl leading-9 font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]">
      Gallery
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
          v-for="(src, index) in service.gallery"
          :key="`${src}-${index}`"
        >
          <img
            :src="src"
            :alt="`${service.title} gallery ${index + 1}`"
            class="block h-auto w-full"
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
