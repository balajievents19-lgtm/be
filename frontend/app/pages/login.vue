<script setup lang="ts">
const route = useRoute()
const { data: settings } = useSettings()
const { customer, loaded, refresh, openLogin } = useCustomerAuth()

useSeoMeta({
  title: 'Login | Balaji Royal Events',
  description: 'Account access for Balaji Royal Events customers and partners.',
  robots: 'noindex, nofollow'
})

const verifiedNotice = computed(() => String(route.query.verified || '') === '1'
  ? 'Your email is verified. Sign in with your password.'
  : '')

const oauthError = computed(() => {
  const code = String(route.query.oauth || '')
  if (!code || code === 'success') {
    return ''
  }
  if (code === 'not_configured') {
    return 'Social login is not configured on the server yet.'
  }
  if (code === 'email_required') {
    return 'That social account did not provide a verified email. Register with email first, then connect it from My Account.'
  }
  return 'Social login could not be completed. Please try again or use email login.'
})

onMounted(async () => {
  if (!loaded.value) {
    await refresh()
  }
  if (customer.value) {
    await navigateTo('/account')
    return
  }
  openLogin()
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
            Use the login dialog to access your Balaji Royal Events customer account.
            Administrators continue to use the separate control panel.
          </p>
          <p
            v-if="verifiedNotice"
            class="mt-4 text-sm text-brand-500"
          >
            {{ verifiedNotice }}
          </p>
          <p
            v-if="oauthError"
            class="mt-4 text-sm text-red-600"
            role="alert"
          >
            {{ oauthError }}
          </p>
          <button
            type="button"
            class="mt-8 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-5 py-3 text-white hover:bg-brand-600"
            @click="openLogin()"
          >
            Open Login
          </button>
        </UContainer>
      </section>
    </main>
    <HomeFooter :settings="settings" />
  </div>
</template>
