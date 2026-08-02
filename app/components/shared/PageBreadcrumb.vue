<script setup lang="ts">
import type { BreadcrumbItem } from '~/data/about'

defineProps<{
  items: BreadcrumbItem[]
}>()
</script>

<template>
  <nav
    class="breadcrumb"
    aria-label="Breadcrumb"
  >
    <ol class="m-0 flex list-none flex-wrap items-center p-0">
      <li
        v-for="(item, index) in items"
        :key="`${item.label}-${index}`"
        class="inline-block py-0 pr-0 pl-1.5 text-[13px] leading-5 text-[#333333] first:pl-0"
      >
        <NuxtLink
          v-if="item.to"
          :to="item.to"
          class="pr-[7px] text-[#333333] transition-colors hover:text-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
        >
          {{ item.label }}
        </NuxtLink>
        <span
          v-else
          class="text-brand-500"
          aria-current="page"
        >
          {{ item.label }}
        </span>
      </li>
    </ol>
  </nav>
</template>
