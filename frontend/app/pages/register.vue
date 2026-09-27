<script setup lang="ts">
const { data: settings } = useSettings()
const { openRegister, customer, loaded, refresh } = useCustomerAuth()

useSeoMeta({
  title: 'Registration | Balaji Royal Events',
  description: 'Create a Balaji Royal Events customer account.',
  robots: 'noindex, nofollow'
})

onMounted(async () => {
  if (!loaded.value) {
    await refresh()
  }
  if (customer.value) {
    await navigateTo('/account')
    return
  }
  openRegister()
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
        title="Registration"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Registration' }]"
      />
      <section class="py-16">
        <UContainer class="mx-auto max-w-[800px] text-center">
          <p class="m-0 text-base leading-7 text-[#555]">
            Create your customer account to download gallery originals and manage your profile.
          </p>
          <button
            type="button"
            class="mt-8 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white hover:bg-brand-600"
            @click="openRegister()"
          >
            Open Registration
          </button>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
