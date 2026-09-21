<script setup lang="ts">
import type { FaqItem } from '~/types/home'

withDefaults(defineProps<{
  items?: FaqItem[]
  pending?: boolean
  failed?: boolean
}>(), {
  items: () => [],
  pending: false,
  failed: false
})
</script>

<template>
  <section
    v-if="items.length"
    class="bg-white py-16"
    aria-labelledby="home-faq-heading"
  >
    <UContainer class="mx-auto max-w-[900px] px-4">
      <div class="mb-10 text-center">
        <p class="m-0 text-[11px] font-semibold tracking-[0.24em] text-[#b8863b] uppercase">
          FAQ
        </p>
        <h2
          id="home-faq-heading"
          class="mt-3 font-['Domine',Georgia,serif] text-3xl text-[#333]"
        >
          Questions families ask
        </h2>
      </div>
      <p
        v-if="failed && !items.length"
        class="text-center text-sm text-[#666]"
        role="alert"
      >
        FAQs are temporarily unavailable.
      </p>
      <FaqList
        v-else-if="items.length"
        :items="items"
      />
      <div class="mt-4 text-center">
        <NuxtLink
          to="/faq"
          class="text-sm font-semibold text-brand-500 hover:text-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
        >
          View all FAQs
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
