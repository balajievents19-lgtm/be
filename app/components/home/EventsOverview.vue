<script setup lang="ts">
import { ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Keyboard, Navigation } from 'swiper/modules'
import { overviewEvents } from '~/data/home'

import 'swiper/css'
import 'swiper/css/navigation'

const modules = [A11y, Keyboard, Navigation]
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

const onSwiper = (swiper: { slideNext: () => void, slidePrev: () => void }) => {
  swiperRef.value = swiper
}

const goNext = () => {
  swiperRef.value?.slideNext()
}

const goPrev = () => {
  swiperRef.value?.slidePrev()
}
</script>

<template>
  <section
    class="home-event relative py-[46px] pb-[95px]"
    aria-labelledby="events-overview-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="w-full text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-[#f15b22]"
            aria-hidden="true"
          />
        </div>

        <div class="relative mx-auto my-7 mb-[23px] inline-block w-full max-w-[761px]">
          <div
            class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
            aria-hidden="true"
          />
          <h2
            id="events-overview-heading"
            class="relative z-[2] m-0 inline-block bg-white px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Events Overview
          </h2>
        </div>

        <p class="info-text mx-auto m-0 inline-block w-full max-w-[761px] text-center text-sm leading-7 text-[#6a6767]">
          It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
        </p>
      </div>

      <div class="relative mt-0 max-[991px]:px-[15px]">
        <Swiper
          :modules="modules"
          :slides-per-view="1"
          :space-between="0"
          :speed="600"
          :breakpoints="{
            768: { slidesPerView: 2 },
            991: { slidesPerView: 3 }
          }"
          :keyboard="{ enabled: true }"
          :a11y="{ enabled: true }"
          class="event-slider w-full"
          @swiper="onSwiper"
        >
          <SwiperSlide
            v-for="(event, index) in overviewEvents"
            :key="`event-${index}-${event.title}`"
          >
            <div class="px-[15px]">
              <article class="event-box block pt-[42px] text-center">
                <div class="group relative mb-[22px] block w-full bg-black text-center">
                  <NuxtLink
                    :to="event.to"
                    class="relative block"
                    :aria-label="event.title"
                  >
                    <img
                      :src="event.image"
                      :alt="event.title"
                      width="370"
                      height="300"
                      class="mx-auto block h-auto w-full max-w-full transition-opacity duration-[350ms] ease-in-out group-hover:opacity-70"
                      loading="lazy"
                      decoding="async"
                    >

                    <span
                      class="capsan pointer-events-none absolute inset-0 h-full w-full scale-0 text-center opacity-0 transition-[opacity,transform] duration-[350ms] ease-in-out group-hover:scale-100 group-hover:opacity-100 group-focus-within:scale-100 group-focus-within:opacity-100"
                      aria-hidden="true"
                    >
                      <span
                        class="absolute inset-[30px] border border-solid border-white"
                        aria-hidden="true"
                      />
                      <span
                        class="absolute top-1/2 left-0 mt-[-15px] w-full text-center text-sm leading-[30px] text-white"
                      >
                        {{ event.caption }}
                      </span>
                    </span>
                  </NuxtLink>
                </div>

                <div
                  class="name mb-2 block text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-7 text-[#333333]"
                >
                  {{ event.title }}
                </div>

                <p
                  :class="[
                    'm-0 inline-block text-center text-sm leading-6 text-[#888888]',
                    index === 0 ? 'text-justify' : ''
                  ]"
                >
                  {{ event.description }}
                </p>

                <NuxtLink
                  :to="event.to"
                  class="block text-sm leading-6 text-[#f15b25] hover:text-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                >
                  Readmore
                </NuxtLink>
              </article>
            </div>
          </SwiperSlide>
        </Swiper>

        <button
          type="button"
          class="absolute top-1/2 left-[-6%] z-[9] mt-[-13px] h-[27px] w-[27px] rounded-full text-[#c9c9c9] transition-colors hover:text-[#888888] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22] max-[1399px]:left-[-15px] max-[991px]:left-0"
          aria-label="Previous events"
          @click="goPrev"
        >
          <span
            class="icon icon-arrow-left inline-block w-[27px] text-center text-xl leading-[27px]"
            aria-hidden="true"
          />
        </button>

        <button
          type="button"
          class="absolute top-1/2 right-[-6%] z-[9] mt-[-13px] h-[27px] w-[27px] rounded-full text-[#c9c9c9] transition-colors hover:text-[#888888] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22] max-[1399px]:right-[-15px] max-[991px]:right-0"
          aria-label="Next events"
          @click="goNext"
        >
          <span
            class="icon icon-arrow-right inline-block w-[27px] text-center text-xl leading-[27px]"
            aria-hidden="true"
          />
        </button>
      </div>
    </UContainer>
  </section>
</template>
