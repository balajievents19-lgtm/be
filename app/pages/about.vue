<script setup lang="ts">
import { aboutBreadcrumbs, aboutPageHeader } from '~/data/about'
import { contactBoxesFromSettings } from '~/utils/content'

const { data: settings } = await useSettings()

const about = computed(() => settings.value?.about ?? null)
const contactBoxes = computed(() => contactBoxesFromSettings(settings.value))

useSeoMeta({
  title: () => settings.value?.seo?.meta_title
    ? `About Us | ${settings.value.company?.name || 'Balaji Events'}`
    : 'About Us | Balaji Events',
  description: () => about.value?.description
    || settings.value?.seo?.meta_description
    || settings.value?.company?.description
    || 'Learn about Balaji Events — wedding and event management.',
  ogTitle: 'About Us | Balaji Events',
  ogDescription: () => about.value?.description
    || settings.value?.company?.description
    || undefined,
  ogImage: () => about.value?.image || settings.value?.seo?.opengraph_image || undefined,
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

      <HomeAbout
        :description="about?.description"
        :image="about?.image"
        :show-cta="false"
      />

      <AboutAboutContactSection :boxes="contactBoxes" />
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
