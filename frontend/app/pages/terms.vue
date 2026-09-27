<script setup lang="ts">
const seo = usePageSeo({ type: 'terms' })
const { data: settings } = useSettings()
await seo
const brand = computed(() => settings.value?.company?.name?.trim() || 'Balaji Royal Events')
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
        title="Terms & Conditions"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Terms' }]"
      />
      <section class="py-12 pb-20">
        <UContainer class="mx-auto max-w-[800px]">
          <div class="space-y-5 text-sm leading-7 text-[#555]">
            <p>
              These notes describe how to use the {{ brand }} website. They are not a substitute for a signed event contract.
            </p>
            <p>
              Enquiry forms, quotes, and messages sent through this website are requests for information.
              A booking is confirmed only when both sides agree in writing (or another method our team confirms).
            </p>
            <p>
              Photos, text, logos, and other content on this website belong to {{ brand }} or their rightful owners.
              Please do not copy them for commercial use without permission.
            </p>
            <p>
              We try to keep service descriptions accurate. Packages, availability, and pricing can change.
              Final details are always those confirmed for your event.
            </p>
            <p>
              For questions, use the Contact page or the phone numbers published in the website settings.
            </p>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
