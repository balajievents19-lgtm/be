<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Autoplay, EffectFade, Keyboard, Navigation } from 'swiper/modules'
import HeroSearch from '~/components/home/HeroSearch.vue'
import type { HeroSlide } from '~/types/home'
import type { Service } from '~/types/service'

import 'swiper/css'
import 'swiper/css/effect-fade'
import 'swiper/css/navigation'

const props = withDefaults(defineProps<{
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

const { data: settings } = useSettings()
const heroSearchEnabled = computed(() => settings.value?.hero_search?.enabled !== false)

const modules = [A11y, Autoplay, EffectFade, Keyboard, Navigation]
const prefersReducedMotion = ref(false)
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)
const activeIndex = ref(0)

const activeSlide = computed(() => props.slides[activeIndex.value] ?? props.slides[0] ?? null)

const titleAlignClass = computed(() => {
  const alignment = activeSlide.value?.text_alignment || 'center'
  if (alignment === 'left') {
    return 'text-left'
  }
  if (alignment === 'right') {
    return 'text-right'
  }
  return 'text-center'
})

const isExternalUrl = (url: string) => /^https?:\/\//i.test(url) || url.startsWith('mailto:') || url.startsWith('tel:')

const overlayStyleFor = (opacity: number | null | undefined) => {
  const value = Math.min(100, Math.max(0, Number(opacity ?? 35))) / 100
  return { backgroundColor: `rgba(0, 0, 0, ${value})` }
}

let mediaQuery: MediaQueryList | undefined

const updateReducedMotionPreference = () => {
  prefersReducedMotion.value = mediaQuery?.matches ?? false
}

const onSwiper = (swiper: { slideNext: () => void, slidePrev: () => void, realIndex?: number, activeIndex?: number }) => {
  swiperRef.value = swiper
  activeIndex.value = swiper.realIndex ?? swiper.activeIndex ?? 0
}

const onSlideChange = (swiper: { realIndex?: number, activeIndex?: number }) => {
  activeIndex.value = swiper.realIndex ?? swiper.activeIndex ?? 0
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
        @slide-change="onSlideChange"
      >
        <SwiperSlide
          v-for="(slide, index) in slides"
          :key="slide.id"
        >
          <div class="relative max-h-[850px] w-full overflow-hidden">
            <video
              v-if="slide.video_url"
              :src="slide.video_url"
              class="block h-auto max-h-[850px] w-full object-cover"
              autoplay
              muted
              loop
              playsinline
              :aria-label="slide.title || 'Balaji Events'"
            />
            <picture v-else>
              <source
                v-if="slide.mobile_image"
                :srcset="slide.mobile_image"
                media="(max-width: 991px)"
              >
              <img
                :src="slide.desktop_image"
                :alt="slide.title || 'Balaji Events'"
                :fetchpriority="index === 0 ? 'high' : 'auto'"
                :loading="index === 0 ? 'eager' : 'lazy'"
                decoding="async"
                width="1600"
                height="700"
                class="block h-auto max-h-[850px] w-full object-cover"
              >
            </picture>
            <div
              class="pointer-events-none absolute inset-0 max-[991px]:hidden"
              :style="overlayStyleFor(slide.overlay_opacity)"
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
          class="search-title w-full max-[991px]:bg-none max-[991px]:p-0 max-[991px]:m-0 min-[992px]:mt-[15px] min-[992px]:bg-[url('/images/heading-blackBgimg.png')] min-[992px]:bg-bottom min-[992px]:bg-no-repeat min-[992px]:pb-5 min-[1400px]:mt-[66px] min-[1400px]:pb-[46px]"
          :class="titleAlignClass"
        >
          <h1
            class="m-0 font-light text-black max-[639px]:text-[30px] max-[639px]:leading-10 max-[991px]:text-[40px] max-[991px]:leading-[56px] min-[768px]:max-[991px]:text-[36px] min-[768px]:max-[991px]:leading-[46px] min-[992px]:text-[40px] min-[992px]:leading-[56px] min-[992px]:text-white min-[1400px]:text-[48px] min-[1400px]:leading-[70px]"
          >
            {{ activeSlide?.title || 'Every Event Should be Perfect' }}
          </h1>
          <p
            v-if="activeSlide?.subtitle"
            class="m-0 mt-3 text-base leading-6 text-[#333] min-[992px]:text-lg min-[992px]:leading-7 min-[992px]:text-white/90"
          >
            {{ activeSlide.subtitle }}
          </p>
          <div
            v-if="activeSlide?.button_text && activeSlide?.button_url"
            class="mt-4"
          >
            <a
              v-if="isExternalUrl(activeSlide.button_url)"
              :href="activeSlide.button_url"
              class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-6 py-3 text-base leading-5 text-white transition-colors hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
            >
              {{ activeSlide.button_text }}
            </a>
            <NuxtLink
              v-else
              :to="activeSlide.button_url"
              class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-6 py-3 text-base leading-5 text-white transition-colors hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
            >
              {{ activeSlide.button_text }}
            </NuxtLink>
          </div>
        </div>

        <HeroSearch
          v-if="heroSearchEnabled"
          :services="services"
        />
      </UContainer>
    </div>
  </section>
</template>
