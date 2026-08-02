<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectFade } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/effect-fade'

const modules = [Autoplay, EffectFade]

const slides = [
  {
    image: '/images/slider/slide1.jpg',
    title: 'Luxury Wedding Planning',
    subtitle: 'Creating Unforgettable Celebrations'
  },
  {
    image: '/images/slider/slide2.jpg',
    title: 'Premium Tent House',
    subtitle: 'Elegant Decoration & Event Setup'
  },
  {
    image: '/images/slider/slide3.jpg',
    title: 'Corporate & Family Events',
    subtitle: 'Professional Event Management'
  }
]
</script>

<template>
<section class="relative h-screen overflow-hidden">

<Swiper
:modules="modules"
:slides-per-view="1"
:loop="true"
effect="fade"
:autoplay="{
delay:5000,
disableOnInteraction:false
}"
class="h-full"
>

<SwiperSlide
v-for="slide in slides"
:key="slide.image"
>

<div class="relative h-screen">

<img
:src="slide.image"
:alt="slide.title"
class="absolute inset-0 h-full w-full object-cover"
/>

<div class="absolute inset-0 bg-black/60"/>

<div
class="relative z-10 mx-auto flex h-full max-w-7xl items-center px-6"
>

<div class="max-w-3xl">

<span
class="mb-4 inline-flex rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white"
>
Balaji Events
</span>

<h1
class="text-5xl font-extrabold leading-tight text-white md:text-7xl"
>
{{ slide.title }}
</h1>

<p
class="mt-6 max-w-xl text-lg text-gray-200 md:text-xl"
>
{{ slide.subtitle }}
</p>

<div class="mt-10 flex flex-wrap gap-4">

<UButton
size="xl"
color="primary"
to="/contact"
>
Book Event
</UButton>

<UButton
  size="xl"
  variant="outline"
  color="neutral"
  to="/services"
  class="border-white text-white hover:bg-white hover:text-black"
>
  Our Services
</UButton>

</div>
              <!-- Statistics -->
              <div class="mt-14 grid grid-cols-2 gap-6 md:grid-cols-4">

                <div>
                  <h3 class="text-4xl font-bold text-orange-400">
                    15+
                  </h3>

                  <p class="mt-2 text-sm uppercase tracking-wider text-gray-300">
                    Years Experience
                  </p>
                </div>

                <div>
                  <h3 class="text-4xl font-bold text-orange-400">
                    5000+
                  </h3>

                  <p class="mt-2 text-sm uppercase tracking-wider text-gray-300">
                    Happy Clients
                  </p>
                </div>

                <div>
                  <h3 class="text-4xl font-bold text-orange-400">
                    1200+
                  </h3>

                  <p class="mt-2 text-sm uppercase tracking-wider text-gray-300">
                    Events Completed
                  </p>
                </div>

                <div>
                  <h3 class="text-4xl font-bold text-orange-400">
                    25+
                  </h3>

                  <p class="mt-2 text-sm uppercase tracking-wider text-gray-300">
                    Team Members
                  </p>
                </div>

              </div>

            </div>

          </div>

        </div>

      </SwiperSlide>

    </Swiper>

    <!-- Scroll Indicator -->
    <div
      class="absolute bottom-8 left-1/2 z-20 -translate-x-1/2 animate-bounce"
    >
      <UIcon
        name="i-lucide-chevron-down"
        class="size-8 text-white"
      />
    </div>

  </section>
</template>