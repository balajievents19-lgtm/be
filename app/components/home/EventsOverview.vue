<script setup lang="ts">
const events = [
  {
    title: 'Wedding Planning',
    image: '/images/events/event-1.jpg',
    desc: 'Complete wedding planning with premium decoration, catering and entertainment.'
  },
  {
    title: 'Corporate Events',
    image: '/images/events/event-2.jpg',
    desc: 'Professional conferences, seminars, product launches and business meetings.'
  },
  {
    title: 'Birthday Celebration',
    image: '/images/events/event-3.jpg',
    desc: 'Creative birthday themes, games, photography and complete event management.'
  }
]
</script>

<template>
  <section class="bg-[#f8f8f8] py-20">
    <div class="mx-auto max-w-7xl px-5">
      <div class="mb-14 text-center">
        <p class="mb-2 uppercase tracking-[5px] text-[#F85A1F]">
          Latest Events
        </p>

        <h2 class="text-4xl font-light text-[#222]">
          Events Overview
        </h2>

        <div class="mx-auto mt-5 h-[3px] w-20 bg-[#F85A1F]" />
      </div>

      <div class="grid gap-8 lg:grid-cols-3">
        <article
          v-for="event in events"
          :key="event.title"
          class="overflow-hidden rounded bg-white shadow transition duration-300 hover:-translate-y-2 hover:shadow-xl"
        >
          <img
            :src="event.image"
            :alt="event.title"
            class="h-64 w-full object-cover"
          >

          <div class="p-7">
            <h3
              class="mb-3 text-2xl font-semibold text-[#222]"
            >
              {{ event.title }}
            </h3>

            <p
              class="mb-6 leading-7 text-[#666]"
            >
              {{ event.desc }}
            </p>

            <button
              class="rounded bg-[#F85A1F] px-6 py-3 font-semibold text-white transition hover:bg-[#df4d16]"
            >
              Read More
            </button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
