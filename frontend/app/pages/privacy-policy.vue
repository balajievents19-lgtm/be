<script setup lang="ts">
const seo = usePageSeo({ type: 'privacy' })
const { data: settings } = useSettings()
await seo
const brand = computed(() => settings.value?.company?.name?.trim() || 'Balaji Royal Events')
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="Privacy Policy"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Privacy Policy' }]"
      />
      <section class="py-12 pb-20">
        <UContainer class="mx-auto max-w-[800px]">
          <div class="space-y-5 text-sm leading-7 text-[#555]">
            <p>
              {{ brand }} collects enquiry details such as name, phone, email (if provided), event type,
              event date, event location, budget, and message so our team can respond to wedding and celebration requests.
            </p>
            <p>
              Customer accounts store name, email, and optional profile details needed for login, password reset,
              and gallery original downloads. Social login (Google or Facebook) is used only when you choose that option
              and only if those providers are configured.
            </p>
            <p>
              We use this information to communicate about your enquiry, quotations, and event planning.
              We do not sell personal information. Authorized administrators manage enquiry records in the CRM.
            </p>
            <p>
              Location search on enquiry forms uses your typed address. “Use my location” runs only after you allow
              browser permission. Precise coordinates are not stored with the enquiry unless you enter a place name yourself.
            </p>
            <p>
              Contact forms include a spam honeypot and rate limits. This website may use cookies required for login
              sessions and basic operation.
            </p>
            <p>
              Google reviews, when shown, are retrieved from Google by our server using a Place ID and API key stored
              only on the server. Review text is not edited.
            </p>
            <p>
              For privacy questions, use the Contact page or call the phone numbers listed on this website.
              This page explains current practice; it is not legal advice.
            </p>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
