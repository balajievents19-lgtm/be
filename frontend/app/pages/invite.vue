<script setup lang="ts">
const { data: settings } = useSettings()

useSeoMeta({
  title: 'Invite Friends | Balaji Events',
  description: 'Invite friends and family to plan their celebration with Balaji Events.',
  ogTitle: 'Invite Friends | Balaji Events',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="Invite Friends"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Invite Friends' }]"
      />
      <section class="py-16">
        <UContainer class="mx-auto max-w-[800px] text-center">
          <p class="m-0 text-base leading-7 text-[#555]">
            Know someone planning a wedding, reception, or corporate celebration in Rajasthan?
            Introduce them to Balaji Events and help them start with a complimentary consultation.
          </p>
          <NuxtLink
            to="/contact"
            class="mt-8 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white no-underline hover:bg-brand-600"
          >
            Share an Enquiry
          </NuxtLink>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
