<script setup lang="ts">
const { data: settings } = useSettings()

useSeoMeta({
  title: 'Careers | Balaji Events',
  description: 'Join the Balaji Events team planning weddings and celebrations across Rajasthan.',
  ogTitle: 'Careers | Balaji Events',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="Careers"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Careers' }]"
      />
      <section class="py-16">
        <UContainer class="mx-auto max-w-[800px] text-center">
          <p class="m-0 text-base leading-7 text-[#555]">
            Balaji Events is growing across Jaipur, Udaipur, Jhunjhunu, and destination venues.
            We welcome planners, décor stylists, coordinators, and hospitality professionals who care about detail.
          </p>
          <p class="mt-4 text-sm leading-6 text-[#666]">
            Send your introduction and experience through our contact form. Our team reviews every application.
          </p>
          <NuxtLink
            to="/contact"
            class="mt-8 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white no-underline hover:bg-brand-600"
          >
            Apply via Contact
          </NuxtLink>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
