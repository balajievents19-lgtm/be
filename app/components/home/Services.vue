<script setup lang="ts">
const services = [
  { icon: "icon-caterers", title: "Caterers" },
  { icon: "icon-flower-pot", title: "Decor & Florists" },
  { icon: "icon-calander", title: "Event Planner" },
  { icon: "icon-beauty", title: "Make-up and Hair" },
  { icon: "icon-wedding-card", title: "Wedding Cards" },
  { icon: "icon-mehandi", title: "Mehandi" },
  { icon: "icon-cake", title: "Cakes" },
  { icon: "icon-music", title: "DJ" },
  { icon: "icon-camera", title: "Photographers & Videographers" },
  { icon: "icon-glass", title: "Entertainment" }
]
</script>

<template>
  <section class="bg-[#f5f5f6] py-20">

    <UContainer>

      <!-- Heading -->

      <div class="mb-16 text-center">

        <span
          class="icon icon-heading-icon text-[58px] text-[#f15b25]"
        />

        <div class="relative mx-auto mt-6 max-w-xl">

          <div
            class="absolute top-1/2 left-0 h-px w-full bg-gray-300"
          />

          <h2
            class="relative inline-block bg-[#f5f5f6] px-6 text-4xl font-light text-[#333]"
          >
            Our Services
          </h2>

        </div>

      </div>

      <!-- Grid -->

      <div
        class="grid gap-7 sm:grid-cols-2 lg:grid-cols-5"
      >

        <NuxtLink
          v-for="item in services"
          :key="item.title"
          to="/services"
          class="group bg-white px-5 py-9 text-center shadow-sm transition-all duration-300 hover:-translate-y-2 hover:bg-[#f15b25]"
        >

          <span
            :class="item.icon"
            class="icon mb-6 block text-[58px] text-[#555] transition group-hover:text-white"
          />

          <h3
            class="text-[17px] leading-6 text-[#444] transition group-hover:text-white"
          >
            {{ item.title }}
          </h3>

        </NuxtLink>

      </div>

    </UContainer>

  </section>
</template>