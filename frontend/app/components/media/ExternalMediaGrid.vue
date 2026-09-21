<script setup lang="ts">
import ExternalMediaCard from '~/components/media/ExternalMediaCard.vue'

const props = withDefaults(defineProps<{
  decorated?: boolean
  heading?: string
  description?: string | null
  homepageOnly?: boolean
  limit?: number | null
}>(), {
  decorated: false,
  heading: 'Videos & Media',
  description: 'Watch highlights and social moments from Balaji Royal Events — streamed from YouTube and other platforms (no video files stored on this website).',
  homepageOnly: false,
  limit: null
})

const { data, pending, failed } = useExternalMedia({ homepage: props.homepageOnly })

const items = computed(() => {
  const list = data.value ?? []
  if (props.limit && props.limit > 0) {
    return list.slice(0, props.limit)
  }
  return list
})
</script>

<template>
  <section
    class="overflow-hidden bg-white py-[50px]"
    :aria-label="heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div
        v-if="decorated"
        class="mb-10 w-full text-center"
      >
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
          <h2 class="relative z-[2] m-0 inline-block bg-white px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]">
            {{ heading }}
          </h2>
        </div>
        <p
          v-if="description"
          class="mx-auto mt-2 max-w-[640px] text-sm leading-6 text-[#666]"
        >
          {{ description }}
        </p>
      </div>
      <div
        v-else
        class="mb-10 text-center"
      >
        <h2 class="m-0 font-['Domine',Georgia,serif] text-[28px] font-bold leading-9 text-[#333]">
          {{ heading }}
        </h2>
        <p
          v-if="description"
          class="mx-auto mt-3 max-w-[640px] text-sm leading-6 text-[#666]"
        >
          {{ description }}
        </p>
      </div>

      <p
        v-if="pending"
        class="pb-6 text-center text-sm text-[#666]"
        role="status"
      >
        Loading media…
      </p>
      <p
        v-else-if="failed"
        class="pb-6 text-center text-sm text-[#666]"
        role="alert"
      >
        Unable to load media right now.
      </p>
      <p
        v-else-if="!items.length"
        class="pb-6 text-center text-sm text-[#666]"
      >
        No external media published yet.
      </p>
      <div
        v-else
        class="-mx-3 flex flex-wrap"
      >
        <div
          v-for="item in items"
          :key="item.id"
          class="mb-6 w-full px-3 sm:w-1/2 lg:w-1/3"
        >
          <ExternalMediaCard :item="item" />
        </div>
      </div>

      <div
        v-if="homepageOnly && items.length"
        class="mt-2 flex justify-center"
      >
        <NuxtLink
          to="/media"
          class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-8 py-3 text-sm font-normal uppercase tracking-wide text-white hover:bg-brand-600"
        >
          View All Media
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
