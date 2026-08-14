<script setup lang="ts">
const { data: settings } = useSettings()

useSeoMeta({
  title: 'Login | Balaji Events',
  description: 'Account access for Balaji Events customers and partners.',
  ogTitle: 'Login | Balaji Events',
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <LayoutAppHeader />
    <main id="main-content">
      <SharedPageHeader
        title="Login"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Login' }]"
      />
      <section class="py-16">
        <UContainer class="mx-auto max-w-[800px] text-center">
          <p class="m-0 text-base leading-7 text-[#555]">
            Customer and vendor account login will open with the Balaji Events portal.
            Administrators can already access the control panel separately.
          </p>
          <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <NuxtLink
              to="/contact"
              class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white no-underline hover:bg-brand-600"
            >
              Need Help? Contact Us
            </NuxtLink>
          </div>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
