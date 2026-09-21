<script setup lang="ts">
import type { DateValue } from '@internationalized/date'
import { getLocalTimeZone, parseDate, today } from '@internationalized/date'
import { formatEventDateDisplay } from '~/utils/formFields'

const model = defineModel<string>({ default: '' })

const props = withDefaults(defineProps<{
  id?: string
  required?: boolean
  placeholder?: string
  inputClass?: string
  compact?: boolean
}>(), {
  id: 'event-date',
  required: false,
  placeholder: 'Select Date',
  inputClass: '',
  compact: false
})

const open = ref(false)
const minValue = today(getLocalTimeZone())

const calendarValue = computed<DateValue | undefined>({
  get() {
    if (!model.value || !/^\d{4}-\d{2}-\d{2}$/.test(model.value)) {
      return undefined
    }
    try {
      return parseDate(model.value)
    } catch {
      return undefined
    }
  },
  set(value) {
    if (!value) {
      model.value = ''
      return
    }
    const month = String(value.month).padStart(2, '0')
    const day = String(value.day).padStart(2, '0')
    model.value = `${value.year}-${month}-${day}`
    open.value = false
  }
})

const displayValue = computed(() => formatEventDateDisplay(model.value) || props.placeholder)

const fieldClass = computed(() => props.inputClass || (
  props.compact
    ? 'box-border h-[38px] w-full rounded-[3px] border border-solid border-[#cccccc] bg-white py-[5px] pr-2.5 pl-[38px] text-left text-[13px] leading-[26px] text-[#333] outline-none'
    : 'box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-left text-base leading-5 text-[#333] outline-none'
))

const isDateDisabled = (date: DateValue) => date.compare(minValue) < 0

const onSelect = (value: DateValue | DateValue[] | { start?: DateValue, end?: DateValue } | null | undefined) => {
  if (!value || Array.isArray(value) || !('year' in value)) {
    return
  }
  calendarValue.value = value
}
</script>

<template>
  <div class="relative inline-block w-full">
    <span
      class="icon icon-calander-month pointer-events-none absolute top-0 left-0 z-20 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
      aria-hidden="true"
    />
    <UPopover
      v-model:open="open"
      :content="{ side: 'bottom', align: 'start', sideOffset: 6 }"
    >
      <button
        :id="id"
        type="button"
        :class="[
          fieldClass,
          !model ? 'text-[#888]' : '',
          'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500'
        ]"
        :aria-required="required || undefined"
        :aria-expanded="open"
        aria-haspopup="dialog"
        aria-label="Event date"
        @click="open = true"
      >
        {{ displayValue }}
      </button>

      <template #content>
        <div class="rounded-md border border-[#e5e5e5] bg-white p-3 shadow-lg">
          <UCalendar
            :model-value="calendarValue"
            :min-value="minValue"
            :is-date-disabled="isDateDisabled"
            color="primary"
            class="w-full"
            @update:model-value="onSelect"
          />
        </div>
      </template>
    </UPopover>

    <!-- Keep a native value for form serialization / progressive enhancement -->
    <input
      v-model="model"
      type="hidden"
      name="date"
      :required="required"
    >
  </div>
</template>
