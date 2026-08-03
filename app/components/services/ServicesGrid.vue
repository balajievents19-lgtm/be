<script setup lang="ts">
import { computed } from 'vue'
import ServiceCard from '~/components/services/ServiceCard.vue'
import type { Service } from '~/types/service'
import { useServices } from '~/composables/useServices'

const props = withDefaults(defineProps<{
  homepageOnly?: boolean
  filterQuery?: string
  /** When provided (homepage aggregator), skip the services list request. */
  services?: Service[] | null
  pending?: boolean
  failed?: boolean
}>(), {
  homepageOnly: false,
  filterQuery: '',
  services: undefined,
  pending: undefined,
  failed: undefined
})

const hasProvidedServices = computed(() => props.services !== undefined && props.services !== null)

const fetched = hasProvidedServices.value
  ? null
  : await useServices()

const services = computed(() =>
  hasProvidedServices.value
    ? (props.services ?? [])
    : (fetched?.data.value ?? [])
)

const pending = computed(() =>
  props.pending !== undefined
    ? props.pending
    : (fetched?.pending.value ?? false)
)

const failed = computed(() =>
  props.failed !== undefined
    ? props.failed
    : (fetched?.failed.value ?? false)
)

const visibleServices = computed(() => {
  let list: Service[] = services.value ?? []

  if (props.homepageOnly) {
    list = list.filter(item => item.show_on_homepage)
  }

  const q = props.filterQuery.trim().toLowerCase()
  if (q) {
    list = list.filter(item =>
      item.name.toLowerCase().includes(q)
      || item.slug.toLowerCase().includes(q)
      || (item.short_description?.toLowerCase().includes(q) ?? false)
    )
  }

  return list
})
</script>

<template>
  <section
    class="service-type bg-surface-muted py-10 pb-[30px]"
    aria-labelledby="services-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="mb-[50px] w-full text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-brand-500"
            aria-hidden="true"
          />
        </div>

        <div class="relative mx-auto mt-7 mb-[23px] inline-block w-full max-w-[761px]">
          <div
            class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
            aria-hidden="true"
          />
          <h2
            id="services-heading"
            class="relative z-[2] m-0 inline-block bg-surface-muted px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Our Services
          </h2>
        </div>
      </div>

      <p
        v-if="pending"
        class="pb-10 text-center text-sm text-[#666]"
        role="status"
      >
        Loading services…
      </p>

      <p
        v-else-if="failed"
        class="pb-10 text-center text-sm text-[#666]"
        role="alert"
      >
        Unable to load services. Please try again later.
      </p>

      <p
        v-else-if="!visibleServices.length"
        class="pb-10 text-center text-sm text-[#666]"
      >
        No services available right now.
      </p>

      <ul
        v-else
        class="service-catagari -mx-[15px] m-0 flex list-none flex-wrap p-0"
        role="list"
      >
        <li
          v-for="item in visibleServices"
          :key="item.id"
          class="w-1/2 px-[15px] pb-[30px] min-[768px]:w-1/5"
        >
          <ServiceCard :item="item" />
        </li>
      </ul>
    </UContainer>
  </section>
</template>
