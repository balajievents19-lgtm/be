<script setup lang="ts">
const props = defineProps<{
  embed?: string | null
}>()

const hasEmbed = computed(() => Boolean(props.embed?.trim()))
</script>

<template>
  <section
    v-if="hasEmbed"
    class="pb-[60px]"
    aria-label="Office location map"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <SharedSafeHtml
        :html="embed"
        profile="map"
        class="contact-map overflow-hidden [&_iframe]:block [&_iframe]:h-[360px] [&_iframe]:w-full md:[&_iframe]:h-[450px]"
      />
    </UContainer>
  </section>
</template>
