<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Autoplay, EffectFade, Keyboard, Navigation } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/effect-fade'
import 'swiper/css/navigation'

const modules = [A11y, Autoplay, EffectFade, Keyboard, Navigation]
const prefersReducedMotion = ref(false)

const slides = [
  '/images/slider/slide1.jpg',
  '/images/slider/slide2.jpg',
  '/images/slider/slide3.jpg'
]

let mediaQuery: MediaQueryList | undefined

const updateReducedMotionPreference = () => {
  prefersReducedMotion.value = mediaQuery?.matches ?? false
}

onMounted(() => {
  mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
  updateReducedMotionPreference()
  mediaQuery.addEventListener('change', updateReducedMotionPreference)
})

onUnmounted(() => {
  mediaQuery?.removeEventListener('change', updateReducedMotionPreference)
})
</script>

<template>
  <section
    class="relative w-full"
    aria-label="Balaji Events"
  >
    <Swiper
      :modules="modules"
      :slides-per-view="1"
      :loop="true"
      effect="fade"
      :speed="600"
      :keyboard="{ enabled: true, onlyInViewport: true }"
      :a11y="{
        prevSlideMessage: 'Previous slide',
        nextSlideMessage: 'Next slide'
      }"
      :navigation="{
        nextEl: '.banner-next',
        prevEl: '.banner-prev'
      }"
      :autoplay="prefersReducedMotion ? false : {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true
      }"
      class="aspect-[1920/850] max-h-[850px]"
    >
      <SwiperSlide
        v-for="(image, index) in slides"
        :key="image"
      >
        <img
          :src="image"
          alt="Balaji Events"
          :fetchpriority="index === 0 ? 'high' : 'auto'"
          :loading="index === 0 ? 'eager' : 'lazy'"
          class="h-full w-full object-cover"
        >
      </SwiperSlide>
    </Swiper>

    <div class="pointer-events-none absolute inset-0 hidden bg-black/50 min-[992px]:block" />

    <button
      type="button"
      class="banner-prev absolute left-[2%] top-1/2 z-10 hidden size-[47px] -translate-y-1/2 items-center justify-center rounded-full border-2 border-white/20 text-xl text-white/20 transition-colors hover:border-white hover:text-white min-[992px]:flex"
      aria-label="Previous slide"
    >
      <span class="icon icon-arrow-left" />
    </button>
    <button
      type="button"
      class="banner-next absolute right-[2%] top-1/2 z-10 hidden size-[47px] -translate-y-1/2 items-center justify-center rounded-full border-2 border-white/20 text-xl text-white/20 transition-colors hover:border-white hover:text-white min-[992px]:flex"
      aria-label="Next slide"
    >
      <span class="icon icon-arrow-right" />
    </button>

    <div class="relative z-10 min-[992px]:absolute min-[992px]:inset-x-0 min-[992px]:top-0">
      <UContainer class="mx-auto max-w-[1170px]">
        <div class="bg-[url('/images/heading-blackBgimg.png')] bg-bottom bg-no-repeat pb-[46px] pt-[15px] text-center min-[992px]:text-white min-[1400px]:pt-[66px]">
          <h1 class="m-0 text-[36px] font-light leading-[46px] text-black min-[992px]:text-[40px] min-[992px]:leading-[56px] min-[992px]:text-white min-[1400px]:text-[48px] min-[1400px]:leading-[70px]">
            Every Event Should be
            <span class="font-serif font-bold">Perfect</span>
          </h1>
        </div>

        <HeroSearch />
      </UContainer>
    </div>
  </section>
</template>
