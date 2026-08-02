<script setup lang="ts">
const slides = [
  "/images/slider/slide1.jpg",
  "/images/slider/slide2.jpg",
  "/images/slider/slide3.jpg"
]

const current = ref(0)

onMounted(() => {
  setInterval(() => {
    current.value = (current.value + 1) % slides.length
  }, 5000)
})
</script>

<template>
  <section class="relative h-[680px] overflow-hidden">

    <!-- Slider -->
    <div class="absolute inset-0">

      <img
        v-for="(slide,index) in slides"
        :key="slide"
        :src="slide"
        class="absolute inset-0 h-full w-full object-cover transition-opacity duration-700"
        :class="index === current ? 'opacity-100' : 'opacity-0'"
      >

      <div class="absolute inset-0 bg-black/45"/>

    </div>

    <!-- Content -->

    <UContainer class="relative z-10 flex h-full items-center">

      <div class="mx-auto max-w-5xl text-center">

        <h1
          class="mb-5 text-6xl font-light leading-tight text-white"
        >
          Every Event Should Be
          <strong class="font-bold">
            Perfect
          </strong>
        </h1>

        <div
          class="mx-auto mb-10 h-[3px] w-28 rounded-full bg-[#f15b25]"
        />

        <UCard
          class="mx-auto max-w-5xl rounded-none border-0 shadow-2xl"
        >

          <div class="grid gap-4 lg:grid-cols-4">

            <UInput
              icon="i-lucide-map-pin"
              placeholder="Location"
              size="xl"
            />

            <USelectMenu
              :items="[
                'Wedding',
                'Birthday',
                'Corporate',
                'Conference'
              ]"
              placeholder="Event Type"
              size="xl"
            />

            <UInput
              icon="i-lucide-calendar"
              placeholder="Event Date"
              size="xl"
            />

            <UButton
              size="xl"
              color="primary"
              block
              class="bg-[#f15b25] hover:bg-[#d94e1b]"
            >
              Search
            </UButton>

          </div>

        </UCard>

      </div>

    </UContainer>

  </section>
</template>