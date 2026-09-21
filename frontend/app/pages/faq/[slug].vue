<script setup lang="ts">
const route = useRoute()
const slug = computed(() => String(route.params.slug || ''))

const seo = usePageSeo({ type: 'faq-detail', slug: slug.value })
const { data: faq, pending, failed } = await useFaq(slug.value)
const { data: settings } = useSettings()
await seo

const breadcrumbs = computed(() => [
  { label: 'Home', to: '/' },
  { label: 'FAQ’s', to: '/faq' },
  { label: faq.value?.question || 'FAQ' }
])

if (!faq.value && !pending.value && !failed.value) {
  throw createError({ statusCode: 404, statusMessage: 'FAQ not found' })
}
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
        :title="faq?.question || 'FAQ’s'"
        :breadcrumbs="breadcrumbs"
      />

      <section
        class="faq-detail bg-white py-10 pb-[60px]"
        aria-label="FAQ detail"
      >
        <UContainer class="mx-auto max-w-[1170px]">
          <p
            v-if="failed && !faq"
            class="m-0 py-8 text-center text-sm text-[#888888]"
            role="alert"
          >
            This FAQ could not be loaded.
          </p>
          <p
            v-else-if="pending && !faq"
            class="m-0 py-8 text-center text-sm text-[#888888]"
          >
            Loading FAQ…
          </p>

          <article
            v-else-if="faq"
            class="mx-auto max-w-[800px]"
          >
            <h1 class="m-0 font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-8 text-[#333333]">
              {{ faq.question }}
            </h1>
            <SharedSafeHtml
              :html="faq.answer"
              class="mt-5 text-sm leading-6 text-[#666666] [&_a]:text-brand-500 [&_p]:m-0 [&_p+p]:mt-2"
            />
            <div class="mt-8">
              <NuxtLink
                to="/faq"
                class="text-sm text-brand-500 no-underline hover:text-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
              >
                ← Back to FAQ’s
              </NuxtLink>
            </div>
          </article>
        </UContainer>
      </section>
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
