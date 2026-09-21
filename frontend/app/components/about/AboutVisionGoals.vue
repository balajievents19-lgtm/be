<script setup lang="ts">
const props = defineProps<{
  vision?: string | null
  mission?: string | null
  journey?: string | null
}>()

const sections = computed(() => {
  const items: { title: string, body: string }[] = []

  if (props.vision?.trim()) {
    items.push({ title: 'Our Vision', body: props.vision.trim() })
  }
  if (props.mission?.trim()) {
    items.push({ title: 'Our Mission', body: props.mission.trim() })
  }
  if (props.journey?.trim()) {
    items.push({ title: 'Our Journey', body: props.journey.trim() })
  }

  return items
})
</script>

<template>
  <section
    v-if="sections.length"
    class="visionGoals py-[67px] pb-[60px]"
    aria-label="About Balaji Royal Events"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="-mx-[15px] flex flex-wrap">
        <div
          v-for="section in sections"
          :key="section.title"
          class="mb-8 w-full px-[15px] md:mb-0"
          :class="sections.length === 1 ? 'md:w-full' : sections.length === 2 ? 'md:w-1/2' : 'md:w-1/3'"
        >
          <h2 class="mb-3.5 font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-6 text-[#333333]">
            {{ section.title }}
          </h2>
          <p class="m-0 whitespace-pre-line text-sm leading-[30px] text-[#6a6767]">
            {{ section.body }}
          </p>
        </div>
      </div>
    </UContainer>
  </section>
</template>
