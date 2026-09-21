<script setup lang="ts">
withDefaults(defineProps<{
  /** When true, use homepage-style icon + line heading chrome. */
  decorated?: boolean
  heading?: string
  /** Pass null/empty to hide the intro description. */
  description?: string | null
}>(), {
  decorated: false,
  heading: 'Our Event Categories',
  description: 'Explore Balaji Royal Events galleries by celebration type.'
})

const { data: categories, pending, failed } = useGalleryCategories()
</script>

<template>
  <section
    class="gallery-section overflow-hidden bg-white py-[50px]"
    :aria-label="heading"
    :aria-labelledby="decorated ? 'gallery-heading' : undefined"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div
        v-if="decorated"
        class="mb-10 w-full text-center"
      >
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
            {{ heading }}
          </h2>
        </div>
      </div>

      <div
        v-else
        class="mb-10 text-center"
      >
        <h2 class="m-0 font-['Domine',Georgia,'Times_New_Roman',serif] text-[28px] font-bold leading-9 text-[#333333]">
          {{ heading }}
        </h2>
        <p
          v-if="description"
          class="mx-auto mt-3 max-w-[640px] text-sm leading-6 text-[#666666]"
        >
          {{ description }}
        </p>
      </div>

      <p
        v-if="pending"
        class="pb-10 text-center text-sm text-[#666]"
        role="status"
      >
        Loading gallery…
      </p>

      <p
        v-else-if="failed"
        class="pb-10 text-center text-sm text-[#666]"
        role="alert"
      >
        Unable to load gallery. Please try again later.
      </p>

      <p
        v-else-if="!categories?.length"
        class="pb-10 text-center text-sm text-[#666]"
      >
        No gallery categories available right now.
      </p>

      <div
        v-else
        class="-mx-[10px] flex flex-wrap"
      >
        <article
          v-for="category in categories"
          :key="category.id"
          class="mb-5 w-full px-[10px] max-[767px]:w-1/2 min-[768px]:w-1/3 min-[992px]:w-1/4"
        >
          <NuxtLink
            :to="`/gallery/${category.slug}`"
            class="group block h-full overflow-hidden rounded-[8px] border border-solid border-[#ececec] bg-white no-underline shadow-[0_6px_18px_rgba(16,15,15,0.06)] transition-shadow duration-300 hover:shadow-[0_10px_28px_rgba(241,91,34,0.16)]"
          >
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
              <span
                class="pointer-events-none absolute inset-0 bg-[rgba(0,0,0,0.35)] opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                aria-hidden="true"
              />
            </div>
            <div class="bg-white px-3 py-3 text-center">
              <h3 class="m-0 font-['Domine',Georgia,'Times_New_Roman',serif] text-base font-semibold text-[#333] transition-colors group-hover:text-brand-500">
                {{ category.name }}
              </h3>
              <p
                v-if="category.image_count != null"
                class="m-0 mt-1 text-xs text-[#888]"
              >
                {{ category.image_count === 0
                  ? 'Photos not added yet'
                  : (category.image_count === 1 ? '1 photo' : `${category.image_count} photos`) }}
              </p>
            </div>
          </NuxtLink>
        </article>
      </div>
    </UContainer>
  </section>
</template>
