<script setup lang="ts">
const seo = usePageSeo({ type: 'events' })
const { data: settings } = useSettings()
const { data: events, pending, failed } = useEventOverviews()
await seo

const eventLink = (item: { link_url: string | null }) => item.link_url || '/contact'
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
        title="Events"
        :breadcrumbs="[{ label: 'Home', to: '/' }, { label: 'Events' }]"
      />

      <section class="py-14 pb-20">
        <UContainer class="mx-auto max-w-[1170px] px-4">
          <p
            v-if="failed"
            class="text-center text-sm text-[#666]"
            role="alert"
          >
            Events are temporarily unavailable.
          </p>
          <p
            v-else-if="pending && !events?.length"
            class="text-center text-sm text-[#666]"
            role="status"
          >
            Loading events…
          </p>
          <p
            v-else-if="!events?.length"
            class="text-center text-sm text-[#666]"
          >
            Event highlights will appear here when published in the admin.
          </p>
          <div
            v-else
            class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
          >
            <article
              v-for="item in events"
              :key="item.id"
              class="overflow-hidden rounded-2xl border border-[#ead9c4] bg-white shadow-[0_8px_24px_rgba(26,18,8,0.06)]"
            >
              <img
                v-if="item.image"
                :src="item.image"
                :alt="item.title"
                class="aspect-[16/10] h-auto w-full object-cover"
                loading="lazy"
                decoding="async"
                width="640"
                height="400"
              >
              <div class="p-5">
                <h2 class="font-['Domine',Georgia,serif] text-xl text-[#222]">
                  {{ item.title }}
                </h2>
                <p
                  v-if="item.caption || item.description"
                  class="mt-2 text-sm leading-6 text-[#555]"
                >
                  {{ item.caption || item.description }}
                </p>
                <NuxtLink
                  v-if="eventLink(item).startsWith('/')"
                  :to="eventLink(item)"
                  class="mt-4 inline-flex text-sm font-semibold text-brand-500 hover:text-brand-600"
                >
                  Learn more
                </NuxtLink>
                <a
                  v-else
                  :href="eventLink(item)"
                  class="mt-4 inline-flex text-sm font-semibold text-brand-500"
                >
                  Learn more
                </a>
              </div>
            </article>
          </div>
        </UContainer>
      </section>
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
