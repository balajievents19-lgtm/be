<script setup lang="ts">
import { computed, ref } from 'vue'
import {
  getServiceBySlug,
  serviceDetailBreadcrumbs,
  servicesPageHeader
} from '~/data/services'

const route = useRoute()
const slug = computed(() => String(route.params.slug ?? ''))
const service = computed(() => getServiceBySlug(slug.value))

const inquiryRef = ref<HTMLElement | null>(null)

const scrollToInquiry = () => {
  inquiryRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

useSeoMeta({
  title: () => service.value ? `${service.value.title} | Balaji Events` : 'Service | Balaji Events',
  description: () => service.value?.description ?? 'Balaji Events service details.',
  ogTitle: () => service.value ? `${service.value.title} | Balaji Events` : 'Service | Balaji Events',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative bg-[#efefef] text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />

    <main
      v-if="service"
      id="main-content"
    >
      <SharedPageHeader
        :title="service.title"
        :breadcrumbs="serviceDetailBreadcrumbs(service.title)"
      />

      <section class="service-view py-[58px] pb-[100px]">
        <UContainer class="mx-auto max-w-[1170px]">
          <div class="-mx-[15px] flex flex-wrap">
            <aside
              ref="inquiryRef"
              class="left-side mb-8 w-full px-[15px] min-[992px]:mb-0 min-[992px]:w-1/3"
            >
              <div class="filter-view mt-0 block w-full bg-white pt-2.5">
                <ServicesServiceInquiryForm :service-title="service.title" />
              </div>
              <div class="mt-5 px-[15px]">
                <NuxtLink
                  to="/services"
                  class="text-sm text-brand-500 underline hover:text-brand-600"
                >
                  ← Back to {{ servicesPageHeader.title }}
                </NuxtLink>
              </div>
            </aside>

            <div class="w-full px-[15px] min-[992px]:w-2/3 min-[992px]:mt-0 max-[991px]:mt-[30px]">
              <ServicesServiceDetailContent
                :service="service"
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
        title="Service Not Found"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Services', to: '/services' }]"
      />
      <p class="mt-8 text-[#666]">
        This service could not be found.
      </p>
      <NuxtLink
        to="/services"
        class="mt-4 inline-block text-brand-500 underline"
      >
        View all services
      </NuxtLink>
    </main>

    <HomeFooter />
  </div>
</template>
