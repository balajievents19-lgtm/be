<script setup lang="ts">
import type { CtaSectionItem } from '~/types/home'

const props = withDefaults(defineProps<{
  items?: CtaSectionItem[]
}>(), {
  items: () => []
})

const cta = computed(() => props.items[0] ?? null)

const isInternal = (url: string) => url.startsWith('/') && !url.startsWith('//')
</script>

<template>
  <section
    v-if="cta"
    class="relative overflow-hidden bg-[#1a1208] py-16 text-white"
    aria-labelledby="home-cta-heading"
  >
    <div
      v-if="cta.background_image"
      class="pointer-events-none absolute inset-0 bg-cover bg-center opacity-30"
      :style="{ backgroundImage: `url(${cta.background_image})` }"
      aria-hidden="true"
    />
    <UContainer class="relative z-[1] mx-auto max-w-[800px] px-4 text-center">
      <h2
        id="home-cta-heading"
        class="font-['Domine',Georgia,serif] text-3xl"
      >
        {{ cta.title }}
      </h2>
      <p
        v-if="cta.subtitle || cta.body"
        class="mx-auto mt-4 max-w-2xl text-base leading-7 text-white/85"
      >
        {{ cta.subtitle || cta.body }}
      </p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <NuxtLink
          v-if="cta.button_text && cta.button_url && isInternal(cta.button_url)"
          :to="cta.button_url"
          class="inline-flex min-h-11 items-center rounded-full bg-brand-500 px-6 text-sm font-semibold uppercase hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        >
          {{ cta.button_text }}
        </NuxtLink>
        <a
          v-else-if="cta.button_text && cta.button_url"
          :href="cta.button_url"
          class="inline-flex min-h-11 items-center rounded-full bg-brand-500 px-6 text-sm font-semibold uppercase hover:bg-brand-600"
        >
          {{ cta.button_text }}
        </a>
        <NuxtLink
          v-if="cta.secondary_button_text && cta.secondary_button_url && isInternal(cta.secondary_button_url)"
          :to="cta.secondary_button_url"
          class="inline-flex min-h-11 items-center rounded-full border border-[#e8c9a0] px-6 text-sm font-semibold uppercase"
        >
          {{ cta.secondary_button_text }}
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
