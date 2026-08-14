<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { FaqItem } from '~/types/home'

const props = withDefaults(defineProps<{
  items?: FaqItem[]
}>(), {
  items: () => []
})

const items = computed(() => props.items)

/** Accordion: first item open by default (Vue interaction; no Bootstrap JS). */
const openId = ref<number | null>(null)

watch(items, (list) => {
  if (list.length && (openId.value === null || !list.some(item => item.id === openId.value))) {
    openId.value = list[0]!.id
  }
  if (!list.length) {
    openId.value = null
  }
}, { immediate: true })

const toggle = (id: number) => {
  openId.value = openId.value === id ? null : id
}

const scrollTop = () => {
  if (!import.meta.client) {
    return
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <section
    class="faq-list px-0 pt-[77px] pb-0 max-[767px]:pt-[30px]"
    aria-label="Frequently asked questions"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <p
        v-if="!items.length"
        class="m-0 pb-16 text-center text-sm text-[#888888]"
      >
        No FAQs to show yet.
      </p>

      <div
        v-for="item in items"
        :id="`faq-${item.id}`"
        :key="item.id"
        class="faq-slide pb-[60px] max-[767px]:pb-[30px]"
      >
        <button
          type="button"
          class="question block w-full border-b border-dashed border-[#333333] pb-2.5 text-left font-['Domine',Georgia,'Times_New_Roman',serif] text-base font-normal leading-[26px] text-[#333333] transition-colors hover:text-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          :aria-expanded="openId === item.id"
          :aria-controls="`faq-${item.id}-ans`"
          @click="toggle(item.id)"
        >
          {{ item.question }}
        </button>

        <div
          v-show="openId === item.id"
          :id="`faq-${item.id}-ans`"
          class="ans pt-3.5"
        >
          <SharedSafeHtml
            :html="item.answer"
            class="m-0 text-sm leading-6 text-[#888888] [&_a]:text-brand-500 [&_p]:m-0 [&_p+p]:mt-2"
          />
          <NuxtLink
            v-if="item.slug"
            :to="`/faq/${item.slug}`"
            class="mt-3 inline-block text-sm leading-6 text-brand-500 underline hover:text-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          >
            View full FAQ
          </NuxtLink>
          <div class="backTo-top mt-5 block">
            <button
              type="button"
              class="text-sm leading-6 text-brand-500 underline hover:text-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
              @click="scrollTop"
            >
              Back to Top
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="items.length"
        class="pb-16 text-center"
      >
        <NuxtLink
          to="/contact"
          class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-10 py-[14px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
        >
          Contact Us
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
