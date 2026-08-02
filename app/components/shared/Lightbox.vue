<script setup lang="ts">
import { computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps<{
  open: boolean
  images: string[]
  index: number
  alt?: string
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'update:index': [value: number]
}>()

const currentSrc = computed(() => props.images[props.index] ?? '')
const currentAlt = computed(() => props.alt ?? `Gallery image ${props.index + 1}`)

const close = () => {
  emit('update:open', false)
}

const prev = () => {
  if (!props.images.length) {
    return
  }
  const nextIndex = props.index <= 0 ? props.images.length - 1 : props.index - 1
  emit('update:index', nextIndex)
}

const next = () => {
  if (!props.images.length) {
    return
  }
  const nextIndex = props.index >= props.images.length - 1 ? 0 : props.index + 1
  emit('update:index', nextIndex)
}

const onKeydown = (event: KeyboardEvent) => {
  if (!props.open) {
    return
  }

  if (event.key === 'Escape') {
    close()
  } else if (event.key === 'ArrowLeft') {
    prev()
  } else if (event.key === 'ArrowRight') {
    next()
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (!import.meta.client) {
      return
    }
    document.body.style.overflow = isOpen ? 'hidden' : ''
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
      v-if="open"
      class="fixed inset-0 z-[10000] flex items-center justify-center"
      role="dialog"
      aria-modal="true"
      aria-label="Image lightbox"
    >
      <button
        type="button"
        class="absolute inset-0 bg-black/80"
        aria-label="Close lightbox"
        @click="close"
      />

      <div class="relative z-10 flex max-h-[90vh] w-full max-w-[1100px] items-center justify-center px-14">
        <button
          type="button"
          class="absolute left-2 top-1/2 z-20 flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
          aria-label="Previous image"
          @click.stop="prev"
        >
          <span
            class="icon icon-arrow-left text-xl"
            aria-hidden="true"
          />
        </button>

        <img
          :src="currentSrc"
          :alt="currentAlt"
          class="max-h-[85vh] max-w-full rounded object-contain"
        >

        <button
          type="button"
          class="absolute right-2 top-1/2 z-20 flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
          aria-label="Next image"
          @click.stop="next"
        >
          <span
            class="icon icon-arrow-right text-xl"
            aria-hidden="true"
          />
        </button>

        <button
          type="button"
          class="absolute -top-2 right-2 z-20 flex size-10 items-center justify-center rounded-full bg-black/50 text-2xl leading-none text-white transition-colors hover:bg-black/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:right-4"
          aria-label="Close lightbox"
          @click="close"
        >
          ×
        </button>
      </div>
    </div>
  </Teleport>
</template>
