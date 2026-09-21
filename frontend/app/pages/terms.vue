<script setup lang="ts">
const seo = usePageSeo({ type: 'terms' })
const { data: settings } = useSettings()
await seo
const brand = computed(() => settings.value?.company?.name?.trim() || 'Balaji Royal Events')
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
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
