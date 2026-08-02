<script setup lang="ts">
import { services } from '~/data/home'
import ServiceCard from '~/components/services/ServiceCard.vue'
</script>

<template>
  <section
    class="service-type bg-surface-muted py-10 pb-[30px]"
    aria-labelledby="services-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="mb-[50px] w-full text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-brand-500"
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
            class="relative z-[2] m-0 inline-block bg-surface-muted px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Our Services
          </h2>
        </div>
      </div>

      <ul
        class="service-catagari -mx-[15px] m-0 flex list-none flex-wrap p-0"
        role="list"
      >
        <li
          v-for="item in services"
          :key="item.title"
          class="w-1/2 px-[15px] pb-[30px] min-[768px]:w-1/5"
        >
          <ServiceCard :item="item" />
        </li>
      </ul>
    </UContainer>
  </section>
</template>
