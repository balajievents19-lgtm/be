<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Autoplay } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/navigation'

const modules = [Navigation, Autoplay]

const slides = [
  '/images/slider/slide1.jpg',
  '/images/slider/slide2.jpg',
  '/images/slider/slide3.jpg'
]
</script>

<template>
  <div class="relative">
    <Swiper
      :modules="modules"
      :slides-per-view="1"
      :loop="true"
      :navigation="true"
      :autoplay="{
        delay: 4000,
        disableOnInteraction: false
      }"
      class="h-[560px]"
    >
      <SwiperSlide
        v-for="image in slides"
        :key="image"
      >
        <div class="relative h-[560px] overflow-hidden">
          <img
            :src="image"
            :alt="image"
            class="h-full w-full object-cover"
          >

          <div class="absolute inset-0 bg-black/45" />
        </div>
      </SwiperSlide>
    </Swiper>
  </div>
</template>
