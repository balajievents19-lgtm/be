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
