<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Autoplay, EffectFade, Keyboard, Navigation } from 'swiper/modules'
import HeroSearch from '~/components/home/HeroSearch.vue'
import type { HeroSlide } from '~/types/home'

import 'swiper/css'
import 'swiper/css/effect-fade'
import 'swiper/css/navigation'

withDefaults(defineProps<{
  slides?: HeroSlide[]
  pending?: boolean
  failed?: boolean
}>(), {
  slides: () => [],
  pending: false,
  failed: false
})

const { data: settings } = useSettings()
const heroSearchEnabled = computed(() => settings.value?.hero_search?.enabled !== false)

const modules = [A11y, Autoplay, EffectFade, Keyboard, Navigation]
const prefersReducedMotion = ref(false)
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

const overlayStyleFor = (opacity: number | null | undefined) => {
  const value = Math.min(100, Math.max(0, Number(opacity ?? 35))) / 100
  return { backgroundColor: `rgba(0, 0, 0, ${value})` }
}

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
    class="banner relative isolate block w-full overflow-hidden bg-[#1a1a2e]"
    aria-label="Featured events"
  >
    <div
      class="pointer-events-none absolute inset-0 z-0"
      aria-hidden="true"
    >
      <p
        v-if="pending && !slides.length"
        class="flex h-full items-center justify-center text-sm text-white/70"
        role="status"
      >
        Loading…
      </p>

      <p
        v-else-if="failed && !slides.length"
        class="flex h-full items-center justify-center text-sm text-white/70"
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
        class="h-full w-full"
        @swiper="onSwiper"
      >
        <SwiperSlide
          v-for="(slide, index) in slides"
          :key="slide.id"
          class="!h-full"
        >
          <div class="relative h-full min-h-full w-full overflow-hidden">
            <video
              v-if="slide.video_url"
              :src="slide.video_url"
              class="absolute inset-0 block h-full w-full object-cover"
              autoplay
              muted
              loop
              playsinline
              :aria-label="slide.title || 'Balaji Royal Events'"
            />
            <picture v-else>
              <source
                v-if="slide.mobile_image"
                :srcset="slide.mobile_image"
                media="(max-width: 991px)"
              >
              <img
                :src="slide.desktop_image"
                :alt="slide.title || 'Balaji Royal Events'"
                :fetchpriority="index === 0 ? 'high' : 'auto'"
                :loading="index === 0 ? 'eager' : 'lazy'"
                decoding="async"
                width="1600"
                height="700"
                class="absolute inset-0 block h-full w-full object-cover"
              >
            </picture>
            <div
              class="pointer-events-none absolute inset-0"
              :style="overlayStyleFor(slide.overlay_opacity)"
            />
          </div>
        </SwiperSlide>
      </Swiper>
    </div>

    <button
      v-if="slides.length > 1"
      type="button"
      class="absolute top-1/2 left-[2%] z-[9] mt-[-23px] hidden h-[47px] w-[47px] items-center justify-center rounded-full border-2 border-[rgba(255,255,255,0.2)] text-xl text-[rgba(255,255,255,0.2)] transition-colors hover:border-white hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white min-[992px]:flex"
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
      class="absolute top-1/2 right-[2%] z-[9] mt-[-23px] hidden h-[47px] w-[47px] items-center justify-center rounded-full border-2 border-[rgba(255,255,255,0.2)] text-xl text-[rgba(255,255,255,0.2)] transition-colors hover:border-white hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white min-[992px]:flex"
      aria-label="Next slide"
      @click="goNext"
    >
      <span
        class="icon icon-arrow-right inline-block w-11 text-center leading-[49px]"
        aria-hidden="true"
      />
    </button>

    <div class="banner-text relative z-[5] w-full">
      <UContainer class="mx-auto max-w-[1170px] px-4 pt-8 pb-10 max-[991px]:pt-6 max-[991px]:pb-8 min-[992px]:pt-12 min-[992px]:pb-12 min-[1400px]:pt-16 min-[1400px]:pb-14">
        <div class="search-title w-full text-center">
          <h1
            class="m-0 font-light text-white drop-shadow-[0_2px_8px_rgba(0,0,0,0.35)] max-[639px]:text-[30px] max-[639px]:leading-10 max-[991px]:text-[36px] max-[991px]:leading-[48px] min-[992px]:text-[40px] min-[992px]:leading-[56px] min-[1400px]:text-[48px] min-[1400px]:leading-[70px]"
          >
            Unforgettable Moments
          </h1>
          <p
            class="m-0 mt-3 text-base leading-6 text-white/90 drop-shadow-[0_1px_4px_rgba(0,0,0,0.35)] min-[992px]:text-lg min-[992px]:leading-7"
          >
            Decor, catering, entertainment — all under one roof
          </p>
        </div>

        <HeroSearch
          v-if="heroSearchEnabled"
        />
      </UContainer>
    </div>
  </section>
</template>

<style scoped>
.banner :deep(.swiper) {
  height: 100%;
  width: 100%;
}
</style>
