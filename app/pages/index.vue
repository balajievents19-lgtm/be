<script setup lang="ts">
const { data: home, pending, failed } = await useHome()

provide('home', home)

const settings = computed(() => home.value?.settings ?? null)
const hero = computed(() => home.value?.hero ?? [])
const featuredServices = computed(() => home.value?.featured_services ?? [])
const featuredGallery = computed(() => home.value?.featured_gallery ?? [])

useSeoMeta({
  title: () => settings.value?.seo?.meta_title || 'Balaji Events | Every Event Should be Perfect',
  description: () => settings.value?.seo?.meta_description
    || 'Balaji Events is a trusted wedding and event management company in Rajasthan offering planning, décor, catering and entertainment services.',
  ogTitle: () => settings.value?.seo?.meta_title || 'Balaji Events',
  ogDescription: () => settings.value?.seo?.meta_description
    || 'Trusted wedding and event management in Rajasthan.',
  ogImage: () => settings.value?.seo?.opengraph_image || undefined,
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
      <HomeHero
        :slides="hero"
        :services="featuredServices"
        :pending="pending"
        :failed="failed"
      />
      <HomeServices
        :services="featuredServices"
        :pending="pending"
        :failed="failed"
      />
      <HomeEventsOverview />
      <HomeGallery
        :items="featuredGallery"
        :pending="pending"
        :failed="failed"
      />
      <HomeTestimonials />
      <HomeLatestNews />
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
