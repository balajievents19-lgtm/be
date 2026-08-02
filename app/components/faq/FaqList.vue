<script setup lang="ts">
import { computed, ref } from 'vue'
import { faqCta, faqItems } from '~/data/faq'
import { useServices } from '~/composables/useServices'

const { data: services } = await useServices()

const items = computed(() =>
  faqItems.map((item) => {
    if (item.id !== 'faq-3') {
      return item
    }

    const names = (services.value ?? []).map(service => service.name).join(', ')
    return {
      ...item,
      answer: names
        ? `Our services include ${names}.`
        : item.answer
    }
  })
)

/** Accordion: first item open by default (Vue interaction; no Bootstrap JS). */
const openId = ref<string | null>(faqItems[0]?.id ?? null)

const toggle = (id: string) => {
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
      <div
        v-for="item in items"
        :id="item.id"
        :key="item.id"
        class="faq-slide pb-[60px] max-[767px]:pb-[30px]"
      >
        <button
          type="button"
          class="question block w-full border-b border-dashed border-[#333333] pb-2.5 text-left font-['Domine',Georgia,'Times_New_Roman',serif] text-base font-normal leading-[26px] text-[#333333] transition-colors hover:text-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          :aria-expanded="openId === item.id"
          :aria-controls="`${item.id}-ans`"
          @click="toggle(item.id)"
        >
          {{ item.question }}
        </button>

        <div
          v-show="openId === item.id"
          :id="`${item.id}-ans`"
          class="ans pt-3.5"
        >
          <p class="m-0 text-sm leading-6 text-[#888888]">
            {{ item.answer }}
          </p>
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

      <div class="pb-16 text-center">
        <NuxtLink
          :to="faqCta.to"
          class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-10 py-[14px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
        >
          {{ faqCta.label }}
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
