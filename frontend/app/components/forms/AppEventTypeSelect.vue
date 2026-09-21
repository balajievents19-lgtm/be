<script setup lang="ts">
import type { EventType } from '~/types/eventType'

const model = defineModel<string>({ default: '' })

withDefaults(defineProps<{
  id?: string
  required?: boolean
  inputClass?: string
}>(), {
  id: 'event-type',
  required: false,
  inputClass: 'box-border h-[50px] w-full appearance-none rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none'
})

const { data: eventTypes, pending } = useEventTypes()

const selected = computed(() =>
  eventTypes.value.find(item => String(item.id) === model.value || item.slug === model.value) ?? null
)

const resolveIcon = (item: EventType | null) => {
  if (item?.icon && item.icon.startsWith('icon-')) {
    return item.icon
  }
  return 'icon-grid-view'
}
</script>

<template>
  <div class="relative inline-block w-full">
    <span
      class="icon pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
      :class="resolveIcon(selected)"
      aria-hidden="true"
    />
    <select
      :id="id"
      v-model="model"
      name="event_type"
      aria-label="Event type"
      :required="required"
      :disabled="pending && !eventTypes.length"
      :class="inputClass"
    >
      <option value="">
        Event Type
      </option>
      <option
        v-for="item in eventTypes"
        :key="item.id"
        :value="String(item.id)"
      >
        {{ item.name }}
      </option>
    </select>
  </div>
</template>
