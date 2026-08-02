<script setup lang="ts">
import { aboutContactBoxes, aboutContactHeading } from '~/data/about'
</script>

<template>
  <ContactContactInfoCards
    heading-id="about-contact-heading"
    :title="aboutContactHeading.title"
    :info="aboutContactHeading.info"
    :boxes="aboutContactBoxes"
  />
</template>
