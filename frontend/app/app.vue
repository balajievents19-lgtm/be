<script setup lang="ts">
const { data: settings } = useSettings()

useHead(() => {
  const favicon = settings.value?.brand?.favicon
  const verification = settings.value?.seo?.google_search_console_verification

  return {
    link: favicon
      ? [
          { rel: 'icon' as const, type: 'image/x-icon', href: favicon },
          { rel: 'shortcut icon' as const, href: favicon }
        ]
      : [],
    meta: verification
      ? [{ name: 'google-site-verification', content: verification }]
      : []
  }
})
</script>

<template>
  <NuxtPage />
  <LayoutWhatsAppFloat />
</template>
