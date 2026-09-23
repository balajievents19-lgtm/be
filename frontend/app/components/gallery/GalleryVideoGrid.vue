<script setup lang="ts">
import { computed, ref } from 'vue'
import GalleryVideoModal from '~/components/gallery/GalleryVideoModal.vue'
import { galleryItemAlt, galleryItemSrc, type GalleryItem } from '~/types/gallery'

const props = defineProps<{
  items: GalleryItem[]
  boxClass?: string
}>()

const active = ref<GalleryItem | null>(null)
const open = ref(false)

const videos = computed(() => props.items)

const openVideo = (item: GalleryItem) => {
  active.value = item
  open.value = true
}
</script>

<template>
  <div
    v-if="videos.length"
    class="gallery-videos"
  >
    <h3 class="mb-4 mt-10 text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-xl font-bold text-[#333333]">
      Videos
    </h3>
    <div class="gallery-row -mx-[2px] flex flex-wrap overflow-hidden">
      <button
        v-for="item in videos"
        :key="item.id"
        type="button"
        class="gallery-box group relative bg-[#e1e8ed] p-[2px]"
        :class="boxClass ?? 'w-1/2 min-[768px]:w-1/4'"
        :aria-label="`Play ${galleryItemAlt(item)}`"
        @click="openVideo(item)"
      >
        <span class="gallery-image-wrapper bg-[#1a1a2e]">
          <img
            v-if="galleryItemSrc(item)"
            :src="galleryItemSrc(item)"
            :alt="galleryItemAlt(item)"
            class="pointer-events-none select-none"
            loading="lazy"
            decoding="async"
            draggable="false"
          >
          <span class="gallery-play-button" aria-hidden="true">▶</span>
        </span>
        <span class="sr-only">{{ item.video_source || 'video' }}</span>
      </button>
    </div>

    <GalleryVideoModal
      v-model:open="open"
      :item="active"
    />
  </div>
</template>
