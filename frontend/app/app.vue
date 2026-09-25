<script setup lang="ts">
const { data: settings } = useSettings()
const { loaded, refresh } = useCustomerAuth()

onMounted(() => {
  if (import.meta.client && !loaded.value) {
    refresh()
  }
})

useHead(() => {
  const favicon = settings.value?.brand?.favicon || '/favicon.ico'
  const verification = settings.value?.seo?.google_search_console_verification
  const usesOptimizedPng = Boolean(settings.value?.brand?.favicon)

  return {
    link: [
      usesOptimizedPng
        ? { rel: 'icon' as const, type: 'image/png', sizes: '48x48', href: favicon }
        : { rel: 'icon' as const, type: 'image/x-icon', href: favicon },
      { rel: 'shortcut icon' as const, type: usesOptimizedPng ? 'image/png' : 'image/x-icon', href: favicon }
    ],
    meta: verification
      ? [{ name: 'google-site-verification', content: verification }]
      : []
  }
})
</script>

<template>
  <NuxtPage />
  <LayoutWhatsAppFloat />
  <!-- Exactly one Login + one Register modal instance for the whole app -->
  <AuthLoginModal />
  <AuthRegisterModal />
</template>
