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

  return {
    link: [
      { rel: 'icon' as const, type: 'image/x-icon', href: favicon },
      { rel: 'shortcut icon' as const, href: favicon }
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
