<script setup lang="ts">
import { overviewEvents } from '~/data/home'
</script>

<template>
  <section
    class="bg-[#f8f8f8] py-20"
    aria-labelledby="events-overview-heading"
  >
    <div class="mx-auto max-w-7xl px-5">
      <div class="mb-14 text-center">
        <p class="mb-2 uppercase tracking-[5px] text-[#F85A1F]">
          Latest Events
        </p>

        <h2
          id="events-overview-heading"
          class="text-4xl font-light text-[#222]"
        >
          Events Overview
        </h2>

        <div
          class="mx-auto mt-5 h-[3px] w-20 bg-[#F85A1F]"
          aria-hidden="true"
        />
      </div>

      <div class="grid gap-8 lg:grid-cols-3">
        <article
          v-for="event in overviewEvents"
          :key="event.title"
          class="overflow-hidden rounded bg-white shadow transition duration-300 hover:-translate-y-2 hover:shadow-xl"
        >
          <img
            :src="event.image"
            :alt="event.title"
            class="h-64 w-full object-cover"
            loading="lazy"
            decoding="async"
            width="640"
            height="256"
          >

          <div class="p-7">
            <h3 class="mb-3 text-2xl font-semibold text-[#222]">
              {{ event.title }}
            </h3>

            <p class="mb-6 leading-7 text-[#666]">
              {{ event.description }}
            </p>

            <NuxtLink
              :to="event.to"
              class="inline-block rounded bg-[#F85A1F] px-6 py-3 font-semibold text-white transition hover:bg-[#df4d16] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F85A1F]"
            >
              Read More
            </NuxtLink>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
