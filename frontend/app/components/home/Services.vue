<script setup lang="ts">
import ServicesGrid from '~/components/services/ServicesGrid.vue'
import type { Service } from '~/types/service'

withDefaults(defineProps<{
  services?: Service[]
  pending?: boolean
  failed?: boolean
}>(), {
  services: () => [],
  pending: false,
  failed: false
})
</script>

<template>
  <ServicesGrid
    :services="services"
    :pending="pending"
    :failed="failed"
    homepage-only
  />
</template>
