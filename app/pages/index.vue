<script setup lang="ts">
import Services from '~/components/home/Services.vue'
import Hero from '~/components/home/Hero.vue'
import About from '~/components/home/About.vue'
import EventsOverview from '~/components/home/EventsOverview.vue'
import Gallery from '~/components/home/Gallery.vue'
import Testimonials from '~/components/home/Testimonials.vue'
import LatestNews from '~/components/home/LatestNews.vue'
import Footer from '~/components/home/Footer.vue'
import LayoutAppHeader from '~/components/layout/AppHeader.vue'
</script>

<template>
  <div>
    <LayoutAppHeader />

    <Hero />

    <Services />

    <About />

    <EventsOverview />

    <Gallery />

    <Testimonials />

    <LatestNews />

    <Footer />
  </div>
</template>
