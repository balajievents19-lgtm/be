<script setup lang="ts">
import { computed, ref } from 'vue'
import { useService, useServices } from '~/composables/useServices'

const route = useRoute()
const slug = computed(() => String(route.params.slug ?? ''))

const { data: service, pending, failed } = await useService(slug)
const { data: allServices } = await useServices()
const { data: settings } = await useSettings()

const relatedNames = computed(() =>
  (allServices.value ?? [])
    .filter(item => item.slug !== slug.value)
    .slice(0, 5)
    .map(item => item.name)
)

const inquiryRef = ref<HTMLElement | null>(null)

const scrollToInquiry = () => {
  inquiryRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

useSeoMeta({
  title: () => {
    const seoTitle = service.value?.seo?.title
    if (seoTitle) {
      return seoTitle
    }
    return service.value ? `${service.value.name} | Balaji Events` : 'Service | Balaji Events'
  },
  description: () =>
    service.value?.seo?.description
    || service.value?.short_description
    || 'Balaji Events service details.',
  ogTitle: () => service.value ? `${service.value.name} | Balaji Events` : 'Service | Balaji Events',
  ogDescription: () =>
    service.value?.seo?.description
    || service.value?.short_description
    || undefined,
  ogImage: () => service.value?.seo?.opengraph_image || service.value?.featured_image || undefined,
  twitterCard: 'summary_large_image'
})

useHead(() => {
  const base = settings.value?.seo?.canonical_url?.replace(/\/$/, '')
  if (!base || !slug.value) {
    return {}
  }
  return {
    link: [{ rel: 'canonical' as const, href: `${base}/services/${slug.value}` }]
  }
})
</script>

<template>
  <div class="page relative bg-[#efefef] text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />

    <main
      v-if="pending"
      id="main-content"
      class="px-4 py-20 text-center"
    >
      <p
        class="text-sm text-[#666]"
        role="status"
      >
        Loading service…
      </p>
    </main>

    <main
      v-else-if="service"
      id="main-content"
    >
      <SharedPageHeader
        :title="service.name"
        :breadcrumbs="[
          { label: 'Home', to: '/' },
          { label: 'Services', to: '/services' },
          { label: service.name }
        ]"
      />

      <section class="service-view py-[58px] pb-[100px]">
        <UContainer class="mx-auto max-w-[1170px]">
          <div class="-mx-[15px] flex flex-wrap">
            <aside
              ref="inquiryRef"
              class="left-side mb-8 w-full px-[15px] min-[992px]:mb-0 min-[992px]:w-1/3"
            >
              <div class="filter-view mt-0 block w-full bg-white pt-2.5">
                <ServicesServiceInquiryForm :service-title="service.name" />
              </div>
              <div class="mt-5 px-[15px]">
                <NuxtLink
                  to="/services"
                  class="text-sm text-brand-500 underline hover:text-brand-600"
                >
                  ← Back to Services
                </NuxtLink>
              </div>
            </aside>

            <div class="w-full px-[15px] min-[992px]:w-2/3 min-[992px]:mt-0 max-[991px]:mt-[30px]">
              <ServicesServiceDetailContent
                :service="service"
                :related-names="relatedNames"
                @inquire="scrollToInquiry"
              />
              <ServicesServiceGallery :service="service" />
            </div>
          </div>
        </UContainer>
      </section>
    </main>

    <main
      v-else
      id="main-content"
      class="px-4 py-20 text-center"
    >
      <SharedPageHeader
        :title="failed ? 'Unable to Load Service' : 'Service Not Found'"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Services', to: '/services' }]"
      />
      <p class="mt-8 text-[#666]">
        {{ failed ? 'Unable to load this service. Please try again later.' : 'This service could not be found.' }}
      </p>
      <NuxtLink
        to="/services"
        class="mt-4 inline-block text-brand-500 underline"
      >
        View all services
      </NuxtLink>
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
