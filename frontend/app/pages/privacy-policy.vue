<script setup lang="ts">
const { data: settings } = useSettings()

useSeoMeta({
  title: 'Privacy Policy | Balaji Events',
  description: 'Privacy policy for Balaji Events website enquiries and communications.',
  ogTitle: 'Privacy Policy | Balaji Events',
  twitterCard: 'summary_large_image'
})
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
              Balaji Events collects enquiry details such as name, phone, email, event type, event date,
              and event location so our team can respond to wedding and celebration requests.
            </p>
            <p>
              We use this information only to communicate about your enquiry, quotations, and event planning.
              We do not sell personal information.
            </p>
            <p>
              Contact forms are protected with basic spam checks. Media and enquiry records are managed by
              authorized Balaji Events administrators.
            </p>
            <p>
              For privacy questions, contact us through the Contact page or call the phone numbers listed on this website.
            </p>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
