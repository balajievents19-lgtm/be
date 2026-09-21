<script setup lang="ts">
import { computed } from 'vue'
import type { GalleryCategoryCard } from '~/composables/useGallery'

/** Presentation-only icon classes for known category slugs (not category data). */
const CATEGORY_ICON_BY_SLUG: Record<string, string> = {
  'wedding': 'icon-heart',
  'dj-sound': 'icon-music',
  'tent-house': 'icon-banquet',
  'stage-decoration': 'icon-flower-pot',
  'birthday': 'icon-cake',
  'catering': 'icon-caterers',
  'wedding-planner': 'icon-calander',
  'photography': 'icon-camera',
  'corporate-events': 'icon-meeting',
  'mehndi': 'icon-mehandi',
  'sangeet': 'icon-audio-visual',
  'other-events': 'icon-grid-view'
}

const { data: categories, pending, failed } = useGalleryCategories()

/** Homepage shows the first 4 categories from existing API order. */
const homepageCategories = computed(() => (categories.value ?? []).slice(0, 4))

const categoryIcon = (slug: string) => CATEGORY_ICON_BY_SLUG[slug] || 'icon-thumb-image'

const photoLabel = (category: GalleryCategoryCard) => {
  const count = category.image_count ?? 0
  if (count === 0) {
    return 'Photos not added yet'
  }
  return count === 1 ? '1 photo' : `${count} photos`
}
</script>

<template>
  <section
    class="overflow-hidden bg-white py-[50px]"
    aria-labelledby="gallery-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="w-full text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-brand-500"
            aria-hidden="true"
          />
        </div>

        <div class="relative mx-auto mt-7 mb-[23px] inline-block w-full max-w-[761px]">
          <div
            class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
            aria-hidden="true"
          />
          <h2
            id="gallery-heading"
            class="relative z-[2] m-0 inline-block bg-white px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Gallery
          </h2>
        </div>
      </div>

      <p
        v-if="pending"
        class="mt-10 pb-6 text-center text-sm text-[#666]"
        role="status"
      >
        Loading gallery…
      </p>

      <p
        v-else-if="failed"
        class="mt-10 pb-6 text-center text-sm text-[#666]"
        role="alert"
      >
        Unable to load gallery. Please try again later.
      </p>

      <p
        v-else-if="!homepageCategories.length"
        class="mt-10 pb-6 text-center text-sm text-[#666]"
      >
        No gallery categories available right now.
      </p>

      <template v-else>
        <div class="-mx-[12px] mt-10 flex flex-wrap">
          <article
            v-for="category in homepageCategories"
            :key="category.id"
            class="mb-8 w-full px-[12px] max-[767px]:w-full min-[768px]:w-1/2 min-[992px]:w-1/4"
          >
            <div class="group h-full overflow-hidden rounded-[10px] border border-solid border-[#ececec] bg-white shadow-[0_8px_24px_rgba(16,15,15,0.08)] transition-shadow duration-300 hover:shadow-[0_12px_30px_rgba(241,91,34,0.18)]">
              <div class="relative">
                <div class="relative aspect-[4/3] overflow-hidden bg-[#e1e8ed]">
                  <img
                    v-if="category.cover_image"
                    :src="category.cover_image"
                    :alt="category.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    width="400"
                    height="300"
                    loading="lazy"
                    decoding="async"
                    draggable="false"
                    @contextmenu.prevent
                  >
                  <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-[#d7dee4] text-sm text-[#666]"
                  >
                    {{ category.name }}
                  </div>
                </div>

                <div
                  class="absolute bottom-0 left-1/2 z-[2] flex size-[68px] -translate-x-1/2 translate-y-1/2 items-center justify-center rounded-full border-[3px] border-solid border-white bg-brand-500 text-white shadow-[0_4px_12px_rgba(241,91,34,0.35)]"
                  aria-hidden="true"
                >
                  <i
                    class="icon text-[28px] leading-none"
                    :class="categoryIcon(category.slug)"
                  />
                </div>
              </div>

              <div class="px-4 pt-12 pb-6 text-center">
                <h3 class="m-0 font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-7 text-[#333333]">
                  {{ category.name }}
                </h3>
                <p class="m-0 mt-2 text-sm leading-5 text-[#888888]">
                  {{ photoLabel(category) }}
                </p>
                <NuxtLink
                  :to="`/gallery/${category.slug}`"
                  class="mt-4 inline-flex items-center justify-center text-sm font-medium text-brand-500 no-underline transition-colors hover:text-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                >
                  View Gallery →
                </NuxtLink>
              </div>
            </div>
          </article>
        </div>

        <div class="mt-2 mb-2 flex justify-center">
          <NuxtLink
            to="/gallery"
            class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-10 py-[14px] text-center text-sm font-normal uppercase leading-5 tracking-wide text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          >
            View All Gallery
          </NuxtLink>
        </div>
      </template>
    </UContainer>
  </section>
</template>
