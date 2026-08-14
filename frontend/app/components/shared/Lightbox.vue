<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'

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

const closeButtonRef = ref<HTMLButtonElement | null>(null)
const currentSrc = computed(() => props.images[props.index] ?? '')
const currentAlt = computed(() => props.alt ?? `Gallery image ${props.index + 1}`)
const hasMultiple = computed(() => props.images.length > 1)

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
    event.preventDefault()
    close()
  } else if (event.key === 'ArrowLeft') {
    event.preventDefault()
    prev()
  } else if (event.key === 'ArrowRight') {
    event.preventDefault()
    next()
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
      v-if="open"
      class="fixed inset-0 z-[10000] flex items-center justify-center overflow-hidden"
      role="dialog"
      aria-modal="true"
      aria-label="Image viewer"
    >
      <button
        type="button"
        class="absolute inset-0 bg-black/92"
        aria-label="Close image viewer"
        @click="close"
      />

      <button
        ref="closeButtonRef"
        type="button"
        class="absolute top-3 right-3 z-30 flex size-11 items-center justify-center rounded-full bg-black/70 text-3xl leading-none text-white shadow-lg transition-colors hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:top-5 sm:right-5 sm:size-12"
        aria-label="Close image viewer"
        @click.stop="close"
      >
        ×
      </button>

      <!-- Shrink-wrap to the rendered image so nav sits beside it -->
      <div class="relative z-10 inline-flex max-h-[92vh] max-w-[92vw] items-center justify-center">
        <button
          v-if="hasMultiple"
          type="button"
          class="absolute top-1/2 left-2 z-20 flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/65 text-white shadow-md transition-colors hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:left-3 sm:size-12"
          aria-label="Previous image"
          @click.stop="prev"
        >
          <span
            class="icon icon-arrow-left text-xl sm:text-2xl"
            aria-hidden="true"
          />
        </button>

        <!--
          Explicit height forces upscale to ~90vh; width follows aspect ratio;
          max-width keeps landscape images inside the viewport.
        -->
        <img
          :src="currentSrc"
          :alt="currentAlt"
          class="block h-[90vh] w-auto max-h-[92vh] max-w-[92vw] object-contain object-center select-none max-md:h-[90dvh] max-md:max-w-[90vw]"
          loading="eager"
          decoding="async"
          draggable="false"
          @click.stop
          @contextmenu.prevent
        >

        <button
          v-if="hasMultiple"
          type="button"
          class="absolute top-1/2 right-2 z-20 flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/65 text-white shadow-md transition-colors hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:right-3 sm:size-12"
          aria-label="Next image"
          @click.stop="next"
        >
          <span
            class="icon icon-arrow-right text-xl sm:text-2xl"
            aria-hidden="true"
          />
        </button>
      </div>
    </div>
  </Teleport>
</template>
