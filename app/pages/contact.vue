<script setup lang="ts">
import { contactBreadcrumbs, contactPageHeader } from '~/data/contact'
import { contactBoxesFromSettings } from '~/utils/content'

const { data: settings } = await useSettings()

const contactBoxes = computed(() => contactBoxesFromSettings(settings.value))

useSeoMeta({
  title: () => settings.value?.seo?.meta_title
    ? `Contact Us | ${settings.value.company?.name || 'Balaji Royal Events'}`
    : 'Contact Us | Balaji Royal Events',
  description: () => settings.value?.seo?.meta_description
    || settings.value?.company?.description
    || 'Contact Balaji Royal Events. Phone, address, email, and contact form.',
  ogTitle: 'Contact Us | Balaji Royal Events',
  ogDescription: () => settings.value?.seo?.meta_description
    || settings.value?.company?.description
    || undefined,
  ogImage: () => settings.value?.seo?.opengraph_image || undefined,
  twitterCard: 'summary_large_image'
})

useHead(() => ({
  link: settings.value?.seo?.canonical_url
    ? [{ rel: 'canonical' as const, href: `${settings.value.seo.canonical_url.replace(/\/$/, '')}/contact` }]
    : []
}))
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
        :title="contactPageHeader.title"
        :breadcrumbs="contactBreadcrumbs"
      />

      <ContactContactInfoCards :boxes="contactBoxes" />

      <ContactContactForm />
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
