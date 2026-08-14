<script setup lang="ts">
import type { ContactBox } from '~/data/contact'

withDefaults(defineProps<{
  boxes?: ContactBox[]
  title?: string
  info?: string
}>(), {
  boxes: () => [],
  title: 'Contact Us',
  info: ''
})
</script>

<template>
  <ContactInfoCards
    heading-id="about-contact-heading"
    :title="title"
    :info="info"
    :boxes="boxes"
  />
</template>
