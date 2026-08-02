<script setup lang="ts">
import { aboutBreadcrumbs, aboutPageHeader } from '~/data/about'

useSeoMeta({
  title: 'About Us | Balaji Events',
  description: 'Learn about Balaji Events — trusted wedding and event management in Rajasthan. Contact details and company information.',
  ogTitle: 'About Us | Balaji Events',
  ogDescription: 'Trusted wedding and event management in Rajasthan.',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <a
      href="#main-content"
      class="absolute left-[-10000px] top-auto z-[10001] h-px w-px overflow-hidden focus:left-2 focus:top-2 focus:h-auto focus:w-auto focus:overflow-visible focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:text-navy-500 focus:outline focus:outline-2 focus:outline-brand-500"
    >
      Skip to main content
    </a>

    <LayoutAppHeader />

    <main id="main-content">
      <SharedPageHeader
        :title="aboutPageHeader.title"
        :breadcrumbs="aboutBreadcrumbs"
      />

      <!-- Existing About Us split section (master .aboutUs / .ourText) -->
      <HomeAbout :show-cta="false" />

      <!-- Master about.html contact boxes section -->
      <AboutAboutContactSection />
    </main>

    <HomeFooter />
  </div>
</template>
