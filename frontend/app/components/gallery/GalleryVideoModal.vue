<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import type { GalleryItem } from '~/types/gallery'
import { isSafeGalleryEmbedUrl, isSafeGalleryOpenUrl } from '~/utils/galleryVideo'

const props = defineProps<{
  open: boolean
  item: GalleryItem | null
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const closeButtonRef = ref<HTMLButtonElement | null>(null)

const embedUrl = computed(() => {
  const url = props.item?.embed?.embed_url
  return props.item?.embed?.mode === 'embed' && isSafeGalleryEmbedUrl(url) ? url : null
})

const openUrl = computed(() => {
  const url = props.item?.embed?.open_url || props.item?.video_url
  return isSafeGalleryOpenUrl(url) ? url : null
})

const cta = computed(() => props.item?.embed?.cta_label || 'Watch video')

const close = () => {
  emit('update:open', false)
}

const onKeydown = (event: KeyboardEvent) => {
  if (!props.open) {
    return
  }
  if (event.key === 'Escape') {
    event.preventDefault()
    close()
  }
}

watch(
  () => props.open,
  async (isOpen) => {
    if (!import.meta.client) {
      return
    }
    document.body.style.overflow = isOpen ? 'hidden' : ''
    if (isOpen) {
      await nextTick()
      closeButtonRef.value?.focus()
    }
  }
)

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  if (import.meta.client) {
    document.body.style.overflow = ''
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open && item"
      class="fixed inset-0 z-[10000] flex items-center justify-center overflow-hidden"
      role="dialog"
      aria-modal="true"
      :aria-label="item.title || 'Gallery video'"
    >
      <button
        type="button"
        class="absolute inset-0 bg-black/92"
        aria-label="Close video"
        @click="close"
      />

      <button
        ref="closeButtonRef"
        type="button"
        class="absolute top-3 right-3 z-30 flex size-11 items-center justify-center rounded-full bg-black/70 text-3xl leading-none text-white shadow-lg hover:bg-brand-500 sm:top-5 sm:right-5"
        aria-label="Close video"
        @click.stop="close"
      >
        ×
      </button>

      <div class="relative z-10 w-[92vw] max-w-[920px]">
        <div class="relative aspect-video overflow-hidden bg-black">
          <iframe
            v-if="embedUrl"
            :src="embedUrl"
            :title="item.title"
            class="absolute inset-0 h-full w-full border-0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            referrerpolicy="strict-origin-when-cross-origin"
          />
          <div
            v-else
            class="flex h-full min-h-[240px] flex-col items-center justify-center gap-4 px-6 text-center text-white"
          >
            <p class="m-0 text-base">
              This video is hosted on {{ item.video_source || 'an external site' }}.
            </p>
            <a
              v-if="openUrl"
              :href="openUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="rounded-sm bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
            >
              {{ cta }}
            </a>
            <p
              v-else
              class="m-0 text-sm text-white/70"
            >
              This link cannot be opened safely.
            </p>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
