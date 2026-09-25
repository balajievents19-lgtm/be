<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Autoplay, EffectFade, Keyboard, Navigation } from 'swiper/modules'
import HeroSearch from '~/components/home/HeroSearch.vue'
import type { HeroSlide } from '~/types/home'
import type { Service } from '~/types/service'

import 'swiper/css'
import 'swiper/css/effect-fade'
import 'swiper/css/navigation'

withDefaults(defineProps<{
  slides?: HeroSlide[]
  services?: Service[]
  pending?: boolean
  failed?: boolean
}>(), {
  slides: () => [],
  services: () => [],
  pending: false,
  failed: false
})

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
    class="banner relative w-full max-[1199px]:inline-block"
    aria-label="Featured events"
  >
    <div class="relative w-full">
      <p
        v-if="pending && !slides.length"
        class="py-20 text-center text-sm text-[#666]"
        role="status"
      >
        Loading…
      </p>

      <p
        v-else-if="failed && !slides.length"
        class="py-20 text-center text-sm text-[#666]"
        role="alert"
      >
        Unable to load slides.
      </p>

      <Swiper
        v-else-if="slides.length"
        :modules="modules"
        :slides-per-view="1"
        :loop="slides.length > 1"
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
        class="w-full"
        @swiper="onSwiper"
      >
        <SwiperSlide
          v-for="(slide, index) in slides"
          :key="slide.id"
        >
          <div class="relative max-h-[850px] w-full">
            <img
              :src="slide.desktop_image"
              :alt="slide.title || 'Balaji Royal Events'"
              :fetchpriority="index === 0 ? 'high' : 'auto'"
              :loading="index === 0 ? 'eager' : 'lazy'"
              decoding="async"
              width="1600"
              height="700"
              class="block h-auto max-h-[850px] w-full object-cover"
            >
            <div
              class="pointer-events-none absolute inset-0 bg-[var(--color-hero-overlay)] max-[991px]:hidden"
              aria-hidden="true"
            />
          </div>
        </SwiperSlide>
      </Swiper>

      <button
        v-if="slides.length > 1"
        type="button"
        class="absolute top-1/2 left-[2%] z-[9] mt-[-23px] flex h-[47px] w-[47px] items-center justify-center rounded-full border-2 border-[rgba(255,255,255,0.2)] text-xl text-[rgba(255,255,255,0.2)] transition-colors hover:border-white hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-[991px]:hidden"
        aria-label="Previous slide"
        @click="goPrev"
      >
        <span
          class="icon icon-arrow-left inline-block w-10 text-center leading-[49px]"
          aria-hidden="true"
        />
      </button>
      <button
        v-if="slides.length > 1"
        type="button"
        class="absolute top-1/2 right-[2%] z-[9] mt-[-23px] flex h-[47px] w-[47px] items-center justify-center rounded-full border-2 border-[rgba(255,255,255,0.2)] text-xl text-[rgba(255,255,255,0.2)] transition-colors hover:border-white hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-[991px]:hidden"
        aria-label="Next slide"
        @click="goNext"
      >
        <span
          class="icon icon-arrow-right inline-block w-11 text-center leading-[49px]"
          aria-hidden="true"
        />
      </button>
    </div>

    <div
      class="banner-text z-[5] w-full max-[991px]:relative max-[991px]:static min-[992px]:absolute min-[992px]:top-0 min-[992px]:left-0"
    >
      <UContainer class="mx-auto max-w-[1170px]">
        <div
          class="search-title w-full text-center max-[991px]:bg-none max-[991px]:p-0 max-[991px]:m-0 min-[992px]:mt-[15px] min-[992px]:bg-[url('/images/heading-blackBgimg.png')] min-[992px]:bg-bottom min-[992px]:bg-no-repeat min-[992px]:pb-5 min-[1400px]:mt-[66px] min-[1400px]:pb-[46px]"
        >
          <h1
            class="m-0 font-light text-black max-[639px]:text-[30px] max-[639px]:leading-10 max-[991px]:text-[40px] max-[991px]:leading-[56px] min-[768px]:max-[991px]:text-[36px] min-[768px]:max-[991px]:leading-[46px] min-[992px]:text-[40px] min-[992px]:leading-[56px] min-[992px]:text-white min-[1400px]:text-[48px] min-[1400px]:leading-[70px]"
          >
            Every Event Should be
            <span class="font-bold font-['Domine',Georgia,'Times_New_Roman',serif]">Perfect</span>
          </h1>
        </div>

        <HeroSearch :services="services" />
      </UContainer>
    </div>
  </section>
</template>
