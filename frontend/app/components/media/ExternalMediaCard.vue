<script setup lang="ts">
import type { ExternalMediaItem } from '~/utils/externalMedia'
import { isSafeEmbedUrl, isSafeOpenUrl } from '~/utils/externalMedia'

const props = defineProps<{
  item: ExternalMediaItem
}>()

const isYouTube = computed(() => props.item.provider === 'youtube')
const canEmbed = computed(() => props.item.mode === 'embed' && isSafeEmbedUrl(props.item.embed_url))
const openHref = computed(() => {
  if (isYouTube.value) {
    return null
  }

  return isSafeOpenUrl(props.item.url) ? props.item.url : null
})
const cta = computed(() => {
  if (isYouTube.value) {
    return null
  }

  return props.item.cta_label || 'Open link'
})
</script>

<template>
  <article class="flex h-full flex-col overflow-hidden rounded-[8px] border border-solid border-[#ececec] bg-white shadow-[0_6px_18px_rgba(16,15,15,0.06)]">
    <div class="card-media-frame bg-[#111827]">
      <iframe
        v-if="canEmbed && item.embed_url"
        :src="item.embed_url"
        :title="item.title"
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
      />
      <template v-else>
        <img
          v-if="item.thumbnail"
          :src="item.thumbnail"
          :alt="item.title"
          width="1200"
          height="1200"
          loading="lazy"
          decoding="async"
        >
        <div
          v-else
          class="card-media-fallback flex items-center justify-center bg-[#1f2937] px-4 text-center text-sm text-white/80"
        >
          {{ item.provider }}
        </div>
        <div class="absolute inset-0 flex items-end justify-center bg-gradient-to-t from-black/70 to-transparent p-4">
          <a
            v-if="openHref && cta"
            :href="openHref"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center rounded-sm bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
          >
            {{ cta }}
          </a>
          <span
            v-else
            class="text-sm text-white/80"
          >Unavailable link</span>
        </div>
      </template>
    </div>
    <div class="flex flex-1 flex-col px-4 py-4">
      <p class="m-0 text-xs font-semibold tracking-wide text-brand-500 uppercase">
        {{ item.provider.replace('_', ' ') }}
      </p>
      <h3 class="mt-1 mb-0 font-['Domine',Georgia,serif] text-base font-bold leading-6 text-[#333]">
        {{ item.title }}
      </h3>
      <p
        v-if="item.description"
        class="mt-2 mb-0 line-clamp-3 text-sm leading-5 text-[#666]"
      >
        {{ item.description }}
      </p>
      <a
        v-if="canEmbed && openHref && !isYouTube"
        :href="openHref"
        target="_blank"
        rel="noopener noreferrer"
        class="mt-3 inline-block text-sm font-medium text-brand-500 hover:underline"
      >
        Open on {{ item.provider === 'vimeo' ? 'Vimeo' : 'provider' }}
      </a>
    </div>
  </article>
</template>
