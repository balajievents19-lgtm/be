<script setup lang="ts">
import type { BreadcrumbItem } from '~/data/about'

withDefaults(defineProps<{
  title: string
  breadcrumbs?: BreadcrumbItem[]
}>(), {
  breadcrumbs: undefined
})
</script>

<template>
  <section
    class="page-header m-0 bg-[url('/images/pageHeadBg.jpg')] bg-cover bg-center bg-no-repeat py-[30px] pb-[35px]"
    aria-labelledby="page-header-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <SharedPageBreadcrumb
        v-if="breadcrumbs?.length"
        :items="breadcrumbs"
        class="mb-2"
      />
      <h1
        id="page-header-heading"
        class="my-5 text-[30px] leading-normal font-normal uppercase text-brand-500"
      >
        {{ title }}
      </h1>
    </UContainer>
  </section>
</template>
