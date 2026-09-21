<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import Lightbox from '~/components/shared/Lightbox.vue'
import { galleryItemAlt, galleryItemSrc } from '~/types/gallery'

const props = defineProps<{
  slug: string
}>()

const { data, pending, failed } = await useGalleryCategory(props.slug)

const category = computed(() => data.value?.category ?? null)
const items = computed(() =>
  (data.value?.items ?? []).filter(item => Boolean(galleryItemSrc(item)))
)

const pageSize = 12
const page = ref(1)

const totalPages = computed(() => Math.max(1, Math.ceil(items.value.length / pageSize)))

const pagedItems = computed(() => {
  const start = (page.value - 1) * pageSize
  return items.value.slice(start, start + pageSize)
})

/** Full image URLs for the large viewer (category-scoped; safer public previews only). */
const images = computed(() =>
  items.value
    .map(item => item.image || item.thumbnail || '')
    .filter(Boolean)
)
const itemIds = computed(() =>
  items.value.map(item => (item.image || item.thumbnail ? item.id : null))
)
const downloadAvailable = computed(() =>
  items.value.map(item => Boolean(item.download_available ?? true))
)
const isLightboxOpen = ref(false)
const activeIndex = ref(0)

const activeAlt = computed(() => {
  const item = items.value[activeIndex.value]
  return item ? galleryItemAlt(item) : undefined
})

const openLightbox = (absoluteIndex: number) => {
  activeIndex.value = absoluteIndex
  isLightboxOpen.value = true
}

watch(totalPages, (pages) => {
  if (page.value > pages) {
    page.value = pages
  }
})
const onThumbError = (event: Event) => {
  const el = event.target as HTMLImageElement | null
  if (el) {
    el.style.visibility = 'hidden'
  }
}
</script>

<template>
  <section
    class="gallery-section overflow-hidden py-[50px]"
    :aria-label="category ? `${category.name} Gallery` : 'Category gallery'"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="mb-8 text-center">
        <h2 class="m-0 font-['Domine',Georgia,'Times_New_Roman',serif] text-[28px] font-bold leading-9 text-[#333333]">
          {{ category ? `${category.name} Gallery` : 'Gallery' }}
        </h2>
        <p
          v-if="category?.description"
          class="mx-auto mt-3 max-w-[640px] text-sm leading-6 text-[#666666]"
        >
          {{ category.description }}
        </p>
        <NuxtLink
          to="/gallery"
          class="mt-4 inline-block text-sm text-brand-500 no-underline hover:underline"
        >
          ← Back to Our Event Categories
        </NuxtLink>
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
        Unable to load this gallery. Please try again later.
      </p>

      <p
        v-else-if="!items.length"
        class="pb-10 text-center text-sm text-[#666]"
      >
        No images in this category yet.
      </p>

      <div
        v-else
        class="gallery-row -mx-[2px] flex flex-wrap overflow-hidden"
      >
        <button
          v-for="(item, index) in pagedItems"
          :key="item.id"
          type="button"
          class="gallery-box group relative w-1/2 bg-[#e1e8ed] p-[2px] min-[768px]:w-1/4"
          :aria-label="`Open ${galleryItemAlt(item)}`"
          @click="openLightbox((page - 1) * pageSize + index)"
          @contextmenu.prevent
        >
          <img
            :src="galleryItemSrc(item)"
            :alt="galleryItemAlt(item)"
            class="pointer-events-none block h-auto w-full select-none object-cover"
            width="400"
            height="300"
            loading="lazy"
            decoding="async"
            draggable="false"
            @error="onThumbError"
          >

          <span
            class="pointer-events-none absolute inset-0 bg-[rgba(0,0,0,0.5)] opacity-0 transition-opacity duration-500 ease-in-out group-hover:opacity-100 group-focus-visible:opacity-100"
            aria-hidden="true"
          />

          <span
            class="icon icon-search pointer-events-none absolute top-1/2 left-1/2 z-[99] mt-[-20px] ml-[-20px] w-10 scale-0 text-center text-[40px] leading-10 text-white transition-transform duration-500 ease-in-out group-hover:scale-100 group-focus-visible:scale-100"
            aria-hidden="true"
          />
        </button>
      </div>

      <nav
        v-if="totalPages > 1"
        class="mt-8 flex items-center justify-center gap-2"
        aria-label="Gallery pagination"
      >
        <button
          type="button"
          class="rounded border border-[#ddd] px-3 py-1 text-sm disabled:opacity-40"
          :disabled="page <= 1"
          @click="page -= 1"
        >
          Previous
        </button>
        <span class="text-sm text-[#666]">
          Page {{ page }} of {{ totalPages }}
        </span>
        <button
          type="button"
          class="rounded border border-[#ddd] px-3 py-1 text-sm disabled:opacity-40"
          :disabled="page >= totalPages"
          @click="page += 1"
        >
          Next
        </button>
      </nav>
    </UContainer>

    <Lightbox
      v-model:open="isLightboxOpen"
      v-model:index="activeIndex"
      :images="images"
      :item-ids="itemIds"
      :download-available="downloadAvailable"
      :alt="activeAlt"
    />
  </section>
</template>
