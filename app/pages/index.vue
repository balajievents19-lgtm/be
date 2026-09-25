<script setup lang="ts">
const { data: home, pending, failed } = await useHome()

provide('home', home)

const settings = computed(() => home.value?.settings ?? null)
const hero = computed(() => home.value?.hero ?? [])
const featuredServices = computed(() => home.value?.featured_services ?? [])
const eventsOverview = computed(() => home.value?.events_overview ?? [])
const featuredGallery = computed(() => home.value?.featured_gallery ?? [])
const testimonials = computed(() => home.value?.testimonials ?? [])
const successStories = computed(() => home.value?.success_stories ?? [])
const featuredBlog = computed(() => home.value?.featured_blog ?? [])

useSeoMeta({
  title: () => settings.value?.seo?.meta_title || 'Balaji Royal Events | Every Event Should be Perfect',
  description: () => settings.value?.seo?.meta_description
    || settings.value?.company?.description
    || 'Balaji Royal Events wedding and event management.',
  ogTitle: () => settings.value?.seo?.meta_title || settings.value?.company?.name || 'Balaji Royal Events',
  ogDescription: () => settings.value?.seo?.meta_description
    || settings.value?.company?.description
    || undefined,
  ogImage: () => settings.value?.seo?.opengraph_image || undefined,
  twitterCard: 'summary_large_image'
})

useHead(() => {
  const canonical = settings.value?.seo?.canonical_url
  const schema = home.value?.faq_schema
  return {
    link: canonical
      ? [{ rel: 'canonical' as const, href: canonical }]
      : [],
    script: schema
      ? [
          {
            type: 'application/ld+json' as const,
            innerHTML: JSON.stringify(schema)
          }
        ]
      : []
  }
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
      <HomeEventsOverview
        :items="eventsOverview"
        :pending="pending"
        :failed="failed"
      />
      <HomeGallery
        :items="featuredGallery"
        :pending="pending"
        :failed="failed"
      />
      <HomeTestimonials
        :testimonials="testimonials"
        :success-stories="successStories"
        :pending="pending"
        :failed="failed"
      />
      <HomeLatestNews
        :posts="featuredBlog"
        :pending="pending"
        :failed="failed"
      />
    </main>

    <HomeFooter
      :settings="settings"
      :updates="featuredBlog"
    />
  </div>
</template>
