<script setup lang="ts">
import { computed } from 'vue'

const route = useRoute()
const { data: settings } = await useSettings()

const filterQuery = computed(() =>
  String(route.query.search ?? route.query.event_type ?? '')
)

useSeoMeta({
  title: 'Services | Balaji Events',
  description: () => settings.value?.seo?.meta_description
    || 'Explore Balaji Events wedding and event services.',
  ogTitle: 'Services | Balaji Events',
  ogDescription: () => settings.value?.company?.description || undefined,
  twitterCard: 'summary_large_image'
})

const breadcrumbs = [
  { label: 'Home', to: '/' },
  { label: 'Services' }
]
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
        title="Services"
        :breadcrumbs="breadcrumbs"
      />

      <ServicesServicesGrid :filter-query="filterQuery" />
      <ServicesServicesCta />
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
