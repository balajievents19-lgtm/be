<script setup lang="ts">
const seo = usePageSeo({ type: 'media' })
const { data: settings } = useSettings()
await seo
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
        title="Videos & Media"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Media' }]"
      />
      <MediaExternalMediaGrid
        heading="Videos & Media"
        description="External videos and social posts are linked from YouTube, Instagram, Facebook, Google Drive, and more. We do not upload video files to this website."
      />
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
