<script setup lang="ts">
/**
 * Listing CTA — matches master `.btn` / brand primary used across contact & about CTAs.
 * Links to Contact (no invented marketing copy beyond master Contact Us label).
 */
</script>

<template>
  <section
    class="bg-white py-12 pb-16 text-center"
    aria-label="Services contact"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <NuxtLink
        to="/contact"
        class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-10 py-[14px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
      >
        Contact Us
      </NuxtLink>
    </UContainer>
  </section>
</template>
