<script setup lang="ts">
import Services from '~/components/home/Services.vue'
import Hero from '~/components/home/Hero.vue'
import EventsOverview from '~/components/home/EventsOverview.vue'
import LayoutAppHeader from '~/components/layout/AppHeader.vue'
</script>

<template>
  <LayoutAppHeader />

  <Hero />

  <Services />

  <EventsOverview />
</template>