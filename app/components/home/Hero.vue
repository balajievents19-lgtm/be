<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Autoplay, EffectFade, Keyboard, Navigation } from 'swiper/modules'
import HeroSearch from '~/components/home/HeroSearch.vue'
import { heroSlides } from '~/data/home'

import 'swiper/css'
import 'swiper/css/effect-fade'
import 'swiper/css/navigation'

const modules = [A11y, Autoplay, EffectFade, Keyboard, Navigation]
const prefersReducedMotion = ref(false)
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

let mediaQuery: MediaQueryList | undefined

const updateReducedMotionPreference = () => {
  prefersReducedMotion.value = mediaQuery?.matches ?? false
}

const onSwiper = (swiper: { slideNext: () => void, slidePrev: () => void }) => {
  swiperRef.value = swiper
}

const goNext = () => {
  swiperRef.value?.slideNext()
}

const goPrev = () => {
  swiperRef.value?.slidePrev()
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
    class="banner relative w-full"
    aria-label="Featured events"
  >
    <div class="relative w-full">
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
        :autoplay="prefersReducedMotion
          ? false
          : {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
          }"
        class="aspect-[1920/850] max-h-[850px] w-full"
        @swiper="onSwiper"
      >
        <SwiperSlide
          v-for="(slide, index) in heroSlides"
          :key="slide.src"
        >
          <div class="relative h-full max-h-[850px] w-full">
            <img
              :src="slide.src"
              :alt="slide.alt"
              :fetchpriority="index === 0 ? 'high' : 'auto'"
              :loading="index === 0 ? 'eager' : 'lazy'"
              decoding="async"
              width="1920"
              height="850"
              class="h-full w-full object-cover"
            >
            <div
              class="pointer-events-none absolute inset-0 hidden bg-black/50 min-[992px]:block"
              aria-hidden="true"
            />
          </div>
        </SwiperSlide>
      </Swiper>

      <button
        type="button"
        class="absolute left-[2%] top-1/2 z-10 flex size-[47px] -translate-y-1/2 items-center justify-center rounded-full border-2 border-white/20 text-xl text-white/20 transition-colors hover:border-white hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        aria-label="Previous slide"
        @click="goPrev"
      >
        <span
          class="icon icon-arrow-left"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        class="absolute right-[2%] top-1/2 z-10 flex size-[47px] -translate-y-1/2 items-center justify-center rounded-full border-2 border-white/20 text-xl text-white/20 transition-colors hover:border-white hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        aria-label="Next slide"
        @click="goNext"
      >
        <span
          class="icon icon-arrow-right"
          aria-hidden="true"
        />
      </button>
    </div>

    <div
      class="relative z-[5] w-full min-[992px]:absolute min-[992px]:inset-x-0 min-[992px]:top-0"
    >
      <UContainer class="mx-auto max-w-[1170px]">
        <div
          class="text-center min-[992px]:bg-[url('/images/heading-blackBgimg.png')] min-[992px]:bg-bottom min-[992px]:bg-no-repeat min-[992px]:pb-[20px] min-[992px]:pt-[15px] min-[1400px]:pb-[46px] min-[1400px]:pt-[66px]"
        >
          <h1
            class="m-0 text-[36px] font-light leading-[46px] text-black min-[992px]:text-[40px] min-[992px]:leading-[56px] min-[992px]:text-white min-[1400px]:text-[48px] min-[1400px]:leading-[70px]"
          >
            Every Event Should be
            <span class="font-bold font-['Domine',Georgia,'Times_New_Roman',serif]">Perfect</span>
          </h1>
        </div>

        <HeroSearch />
      </UContainer>
    </div>
  </section>
</template>
