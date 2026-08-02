<script setup lang="ts">
import { galleryBreadcrumbs, galleryPageHeader } from '~/data/gallery'

useSeoMeta({
  title: 'Gallery | Balaji Events',
  description: 'Browse Balaji Events gallery photos from weddings and celebrations across Rajasthan.',
  ogTitle: 'Gallery | Balaji Events',
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
        :title="galleryPageHeader.title"
        :breadcrumbs="galleryBreadcrumbs"
      />

      <GalleryGalleryGrid />
    </main>

    <HomeFooter />
  </div>
</template>
