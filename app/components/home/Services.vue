<script setup lang="ts">
import { services } from '~/data/home'
</script>

<template>
  <section
    class="bg-[#f5f5f6] py-10 pb-[30px]"
    aria-labelledby="services-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="mb-[50px] w-full text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-[#f15b22]"
            aria-hidden="true"
          />
        </div>

        <div class="relative mx-auto mt-7 mb-[23px] inline-block w-full max-w-[761px]">
          <div
            class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
            aria-hidden="true"
          />
          <h2
            id="services-heading"
            class="relative z-[2] m-0 inline-block bg-[#f5f5f6] px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Our Services
          </h2>
        </div>
      </div>

      <ul
        class="-mx-[15px] m-0 flex list-none flex-wrap p-0"
        role="list"
      >
        <li
          v-for="item in services"
          :key="item.title"
          class="w-1/2 px-[15px] pb-[30px] md:w-1/5"
        >
          <NuxtLink
            to="/services"
            class="group block bg-white pt-[15px] pb-2.5 text-center text-[#333333] transition-colors duration-300 hover:bg-[#f15b25] hover:text-white focus-visible:bg-[#f15b25] focus-visible:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22] min-[480px]:pt-[27px] md:pt-[15px] lg:pt-[27px]"
            :aria-label="`View ${item.title} services`"
          >
            <span
              :class="[
                'icon',
                item.icon,
                'mx-auto inline-block h-[50px] w-full text-center text-[40px] leading-none transition-colors min-[480px]:h-[68px] min-[480px]:text-[55px] md:h-[50px] md:text-[40px] lg:h-[68px] lg:text-[55px]'
              ]"
              aria-hidden="true"
            />
            <span
              class="mt-1 inline-block min-h-12 px-[5px] text-center text-sm leading-6 min-[480px]:text-base md:text-sm lg:text-base"
            >
              {{ item.title }}
            </span>
          </NuxtLink>
        </li>
      </ul>
    </UContainer>
  </section>
</template>
