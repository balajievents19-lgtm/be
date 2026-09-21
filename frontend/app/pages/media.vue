<script setup lang="ts">
const seo = usePageSeo({ type: 'media' })
const { data: settings } = useSettings()
await seo
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
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
