<script setup lang="ts">
const seo = usePageSeo({ type: 'packages' })
const { data: packages, pending, failed } = await usePackages()
const { data: settings } = useSettings()
await seo

const breadcrumbs = [
  { label: 'Home', to: '/' },
  { label: 'Packages' }
]

/** Prefer Admin-featured packages while preserving relative order. */
const orderedPackages = computed(() => {
  const list = packages.value ?? []
  return [...list].sort((a, b) => {
    const featuredDelta = Number(Boolean(b.is_featured)) - Number(Boolean(a.is_featured))
    if (featuredDelta !== 0) {
      return featuredDelta
    }
    return (a.sort_order ?? 0) - (b.sort_order ?? 0)
  })
})
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <a
      href="#main-content"
      class="absolute left-[-10000px] top-auto z-[10001] h-px w-px overflow-hidden focus:left-2 focus:top-2 focus:h-auto focus:w-auto focus:overflow-visible focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:text-navy-500 focus:outline focus-visible:outline-2 focus-visible:outline-brand-500"
    >
      Skip to main content
    </a>

    <LayoutAppHeader />

    <main id="main-content">
      <SharedPageHeader
        title="Packages"
        :breadcrumbs="breadcrumbs"
      />

      <section
        class="packages-section bg-white py-10 pb-[60px]"
        aria-label="Packages"
      >
        <UContainer class="mx-auto max-w-[1170px]">
          <p
            v-if="failed"
            class="m-0 py-8 text-center text-sm text-[#888888]"
            role="alert"
          >
            Packages are temporarily unavailable.
          </p>
          <p
            v-else-if="pending && !(orderedPackages.length)"
            class="m-0 py-8 text-center text-sm text-[#888888]"
          >
            Loading packages…
          </p>
          <p
            v-else-if="!(orderedPackages.length)"
            class="m-0 py-8 text-center text-sm text-[#888888]"
          >
            Packages will appear here once published.
          </p>

          <div
            v-else
            class="-mx-[15px] flex flex-wrap"
          >
            <article
              v-for="pkg in orderedPackages"
              :key="pkg.id"
              class="mb-[30px] w-full px-[15px] min-[768px]:w-1/2 min-[992px]:w-1/3"
            >
              <div class="h-full border border-solid border-[#e6e6e6] bg-white">
                <div
                  v-if="pkg.image"
                  class="card-media-frame"
                >
                  <img
                    :src="pkg.image"
                    :alt="pkg.name"
                    width="1200"
                    height="1200"
                    loading="lazy"
                    decoding="async"
                  >
                </div>
                <div class="p-5">
                  <h2 class="m-0 font-['Domine',Georgia,'Times_New_Roman',serif] text-xl font-normal leading-7 text-[#333]">
                    {{ pkg.name }}
                  </h2>
                  <p
                    v-if="pkg.price_label"
                    class="mt-2 mb-0 text-base font-semibold text-brand-500"
                  >
                    {{ pkg.price_label }}
                  </p>
                  <p
                    v-if="pkg.summary"
                    class="mt-3 mb-0 text-sm leading-6 text-[#666]"
                  >
                    {{ pkg.summary }}
                  </p>
                  <p
                    v-if="pkg.description"
                    class="mt-3 mb-0 text-sm leading-6 text-[#666]"
                  >
                    {{ pkg.description }}
                  </p>
                  <ul
                    v-if="pkg.features?.length"
                    class="mt-4 mb-0 list-disc pl-5 text-sm leading-6 text-[#555]"
                  >
                    <li
                      v-for="(feature, index) in pkg.features"
                      :key="`${pkg.id}-${index}`"
                    >
                      {{ feature }}
                    </li>
                  </ul>
                  <NuxtLink
                    :to="{ path: '/contact', query: { package: pkg.name } }"
                    class="mt-5 inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-4 py-2 text-sm text-white no-underline transition-colors hover:border-brand-600 hover:bg-brand-600"
                  >
                    Enquire Now
                  </NuxtLink>
                </div>
              </div>
            </article>
          </div>
        </UContainer>
      </section>
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
