<script setup lang="ts">
import { computed, ref } from 'vue'
import Lightbox from '~/components/shared/Lightbox.vue'
import GalleryVideoGrid from '~/components/gallery/GalleryVideoGrid.vue'
import { galleryItemAlt, galleryItemSrc, isGalleryVideo } from '~/types/gallery'

const { data: items, pending, failed } = useGallery()

const photos = computed(() => (items.value ?? []).filter(item => !isGalleryVideo(item) && galleryItemSrc(item)))
const videos = computed(() => (items.value ?? []).filter(isGalleryVideo))
const hasMedia = computed(() => photos.value.length > 0 || videos.value.length > 0)

const images = computed(() => photos.value.map(galleryItemSrc).filter(Boolean))
const itemIds = computed(() => photos.value.map(item => item.id))
const downloadAvailable = computed(() =>
  photos.value.map(item => Boolean(item.download_available ?? true))
)
const isLightboxOpen = ref(false)
const activeIndex = ref(0)

const activeAlt = computed(() => {
  const item = photos.value[activeIndex.value]
  return item ? galleryItemAlt(item) : undefined
})

const openLightbox = (index: number) => {
  activeIndex.value = index
  isLightboxOpen.value = true
}
</script>

<template>
  <section
    class="gallery-section overflow-hidden py-[50px]"
    aria-label="Event gallery"
  >
    <p
      v-if="pending"
      class="mt-[50px] pb-10 text-center text-sm text-[#666]"
      role="status"
    >
      Loading gallery…
    </p>

    <p
      v-else-if="failed"
      class="mt-[50px] pb-10 text-center text-sm text-[#666]"
      role="alert"
    >
      Unable to load gallery. Please try again later.
    </p>

    <p
      v-else-if="!hasMedia"
      class="mt-[50px] pb-10 text-center text-sm text-[#666]"
    >
      No gallery images available right now.
    </p>

    <template v-else>
      <h3
        v-if="photos.length && videos.length"
        class="mb-4 mt-[50px] text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-xl font-bold text-[#333333]"
      >
        Photos
      </h3>
      <div
        v-if="photos.length"
        class="gallery-row -mx-[2px] flex flex-wrap overflow-hidden"
        :class="photos.length && videos.length ? '' : 'mt-[50px]'"
      >
        <button
          v-for="(item, index) in photos"
          :key="item.id"
          type="button"
          class="gallery-box group relative w-1/2 bg-[#e1e8ed] p-[2px] min-[768px]:w-1/5"
          :aria-label="`Open ${galleryItemAlt(item)}`"
          @click="openLightbox(index)"
        >
          <span class="gallery-image-wrapper">
            <img
              :src="galleryItemSrc(item)"
              :alt="galleryItemAlt(item)"
              loading="lazy"
              decoding="async"
              draggable="false"
            >
          </span>

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

      <GalleryVideoGrid
        :items="videos"
        box-class="w-1/2 min-[768px]:w-1/5"
      />
    </template>

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
