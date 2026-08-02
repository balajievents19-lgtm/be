<script setup lang="ts">
useSeoMeta({
  title: 'Balaji Events | Every Event Should be Perfect',
  description: 'Balaji Events is a trusted wedding and event management company in Rajasthan offering planning, décor, catering and entertainment services.',
  ogTitle: 'Balaji Events',
  ogDescription: 'Trusted wedding and event management in Rajasthan.',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative max-md:pt-0 md:pt-[121px]">
    <a
      href="#main-content"
      class="absolute left-[-10000px] top-auto z-[10001] h-px w-px overflow-hidden focus:left-2 focus:top-2 focus:h-auto focus:w-auto focus:overflow-visible focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:text-[#0e1123] focus:outline focus:outline-2 focus:outline-[#f15b22]"
    >
      Skip to main content
    </a>

    <LayoutAppHeader />

    <main id="main-content">
      <HomeHero />
      <HomeServices />
      <HomeEventsOverview />
      <HomeTestimonials />
      <HomeLatestNews />
    </main>

    <HomeFooter />
  </div>
</template>
