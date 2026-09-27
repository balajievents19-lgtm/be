<script setup lang="ts">
const { data: settings } = useSettings()
const canonical = `${useRequestURL().origin}/vendor`

useSeoMeta({
  title: 'Become a Vendor | Balaji Royal Events',
  description: 'Partner with Balaji Royal Events as an approved vendor for weddings and celebrations across Rajasthan.',
  ogTitle: 'Become a Vendor | Balaji Royal Events',
  ogUrl: canonical,
  twitterCard: 'summary_large_image'
})

useHead({
  link: [{ rel: 'canonical', href: canonical }]
})
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
        title="Become a Vendor"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Become a Vendor' }]"
      />
      <section class="py-16">
        <UContainer class="mx-auto max-w-[800px] text-center">
          <p class="m-0 text-base leading-7 text-[#555]">
            Balaji Royal Events works with trusted décor, catering, photography, DJ, and venue partners across Rajasthan.
            Share your profile and portfolio with our team for admin review before any public listing.
          </p>
          <p class="mt-4 text-sm leading-6 text-[#666]">
            Vendor portal features — Profile, Portfolio, Upload, Admin Approval, and Payments — are part of the
            enterprise roadmap. Until then, send your partnership enquiry and our team will respond personally.
          </p>
          <NuxtLink
            to="/contact"
            class="mt-8 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white no-underline hover:bg-brand-600"
          >
            Contact Us
          </NuxtLink>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
