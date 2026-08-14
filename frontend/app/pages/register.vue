<script setup lang="ts">
const { data: settings } = useSettings()

useSeoMeta({
  title: 'Registration | Balaji Events',
  description: 'Register your interest with Balaji Events for weddings, celebrations, and vendor partnerships.',
  ogTitle: 'Registration | Balaji Events',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="Registration"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Registration' }]"
      />
      <section class="py-16">
        <UContainer class="mx-auto max-w-[800px] text-center">
          <p class="m-0 text-base leading-7 text-[#555]">
            Public self-registration will launch with the customer and vendor portals.
            Until then, register your celebration or partnership interest through our enquiry form.
          </p>
          <NuxtLink
            to="/contact"
            class="mt-8 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white no-underline hover:bg-brand-600"
          >
            Register Interest
          </NuxtLink>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
