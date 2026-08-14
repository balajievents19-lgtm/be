<script setup lang="ts">
import { sanitizeCmsHtml, sanitizeMapEmbed } from '~/utils/sanitizeHtml'
import type { SanitizeHtmlProfile } from '~/composables/useSanitizedHtml'

/**
 * Single approved public rendering boundary for stored CMS / map HTML.
 * All public v-html usage must go through this component.
 */
const props = withDefaults(defineProps<{
  html?: string | null
  profile?: SanitizeHtmlProfile
}>(), {
  html: '',
  profile: 'cms'
})

const sanitized = computed(() =>
  props.profile === 'map'
    ? sanitizeMapEmbed(props.html)
    : sanitizeCmsHtml(props.html)
)
</script>

<template>
  <div
    v-if="sanitized"
    v-html="sanitized"
  />
</template>
