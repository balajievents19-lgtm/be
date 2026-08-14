<script setup lang="ts">
const route = useRoute()
const slug = computed(() => String(route.params.slug || ''))

// Start all SSR fetches without blocking layout setup.
const { data, pending, failed } = useGalleryCategory(slug.value)
const { data: settings } = useSettings()
usePageSeo({ type: 'gallery-category', slug: slug.value })

const categoryName = computed(() => data.value?.category?.name || 'Gallery')

const breadcrumbs = computed(() => [
  { label: 'Home', to: '/' },
  { label: 'Gallery', to: '/gallery' },
  { label: categoryName.value }
])
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <a
      href="#main-content"
      class="absolute left-[-10000px] top-auto z-[10001] h-px w-px overflow-hidden focus:left-2 focus:top-2 focus:h-auto focus:w-auto focus:overflow-visible focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:text-navy-500 focus:outline focus-visible:outline-2 focus-visible:outline-brand-500"
    >
      Skip to main content
    </a>

    <LayoutAppHeader />

    <main id="main-content">
      <SharedPageHeader
        :title="`${categoryName} Gallery`"
        :breadcrumbs="breadcrumbs"
      />

      <p
        v-if="failed && !pending"
        class="py-16 text-center text-sm text-[#666]"
        role="alert"
      >
        This gallery category could not be found.
      </p>

      <GalleryCategoryView
        v-else
        :slug="slug"
      />
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
