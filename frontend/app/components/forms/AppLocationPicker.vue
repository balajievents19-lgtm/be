<script setup lang="ts">
import type { HomePayload, OfficeLocationItem } from '~/types/home'
import { buildProgramLocationLabel } from '~/utils/formFields'

interface GeoSuggestion {
  label: string
  latitude?: number | null
  longitude?: number | null
}

const model = defineModel<string>({ default: '' })

withDefaults(defineProps<{
  id?: string
  required?: boolean
  placeholder?: string
  inputClass?: string
}>(), {
  id: 'event-location',
  required: false,
  placeholder: 'Search location...',
  inputClass: 'box-border h-[50px] w-full rounded border border-solid border-[#b8b8b8] bg-white py-[15px] pr-2.5 pl-[38px] text-base leading-5 text-[#333] outline-none'
})

const open = ref(false)
const query = ref('')
const suggestions = ref<GeoSuggestion[]>([])
const searching = ref(false)
const locating = ref(false)
const statusMessage = ref('')
const rootEl = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)

const { data: settings } = useSettings()
const home = useNuxtData<HomePayload>('home')
const base = useApiBase()

let searchTimer: ReturnType<typeof setTimeout> | undefined

const programLocation = computed(() => {
  const offices = home.data.value?.office_locations ?? []
  const primary = offices.find((item: OfficeLocationItem) => item.is_primary) ?? offices[0]
  if (primary) {
    const label = buildProgramLocationLabel(primary)
    if (label) {
      return label
    }
  }

  const address = settings.value?.contact?.address?.trim()
  return address || ''
})

const applyLocation = (value: string) => {
  model.value = value
  query.value = value
  suggestions.value = []
  open.value = false
  statusMessage.value = ''
}

const openPanel = async () => {
  open.value = true
  query.value = model.value
  statusMessage.value = ''
  await nextTick()
  searchInput.value?.focus()
}

const onDocumentPointer = (event: MouseEvent | TouchEvent) => {
  if (!open.value || !rootEl.value) {
    return
  }
  const target = event.target as Node | null
  if (target && !rootEl.value.contains(target)) {
    open.value = false
  }
}

const searchLocations = async (term: string) => {
  const q = term.trim()
  if (q.length < 2) {
    suggestions.value = []
    return
  }

  searching.value = true
  try {
    const response = await laravelFetch<{ data: GeoSuggestion[] }>(`${base}/geo/search`, {
      query: { q }
    })
    suggestions.value = response.data ?? []
  } catch {
    suggestions.value = []
  } finally {
    searching.value = false
  }
}

const onQueryInput = () => {
  model.value = query.value
  statusMessage.value = ''
  if (searchTimer) {
    clearTimeout(searchTimer)
  }
  searchTimer = setTimeout(() => {
    void searchLocations(query.value)
  }, 350)
}

const useCurrentLocation = () => {
  statusMessage.value = ''
  if (!import.meta.client || !navigator.geolocation) {
    statusMessage.value = 'Location is not supported in this browser. Please type your location.'
    return
  }

  locating.value = true
  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const lat = position.coords.latitude
      const lng = position.coords.longitude
      try {
        const response = await laravelFetch<{ data: { label: string } }>(`${base}/geo/reverse`, {
          query: { lat, lng }
        })
        applyLocation(response.data?.label || `${lat.toFixed(5)}, ${lng.toFixed(5)}`)
      } catch {
        applyLocation(`${lat.toFixed(5)}, ${lng.toFixed(5)}`)
      } finally {
        locating.value = false
      }
    },
    (error) => {
      locating.value = false
      if (error.code === error.PERMISSION_DENIED) {
        statusMessage.value = 'Location permission denied. You can still type a location.'
      } else {
        statusMessage.value = 'Unable to detect location. Please type it manually.'
      }
    },
    { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
  )
}

const useProgramLocation = () => {
  if (!programLocation.value) {
    statusMessage.value = 'No program location is configured yet. Please type a location.'
    return
  }
  applyLocation(programLocation.value)
}

onMounted(() => {
  document.addEventListener('mousedown', onDocumentPointer)
  document.addEventListener('touchstart', onDocumentPointer)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onDocumentPointer)
  document.removeEventListener('touchstart', onDocumentPointer)
  if (searchTimer) {
    clearTimeout(searchTimer)
  }
})
</script>

<template>
  <div
    ref="rootEl"
    class="relative block w-full min-w-0"
  >
    <span
      class="icon icon-location-1 pointer-events-none absolute top-0 left-0 z-10 mt-1 h-10 w-[37px] text-center text-lg leading-[48px] text-[#464e7b]"
      aria-hidden="true"
    />
    <button
      :id="id"
      type="button"
      :class="[
        inputClass,
        'min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
        !model ? 'text-[#888]' : 'text-[#333]'
      ]"
      :aria-expanded="open"
      aria-haspopup="dialog"
      aria-label="Event location"
      @click="openPanel"
    >
      <span class="block min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">
        {{ model || 'Event Location' }}
      </span>
    </button>

    <div
      v-if="open"
      class="absolute top-[calc(100%+6px)] left-0 z-30 w-full min-w-0 max-w-full overflow-hidden rounded border border-[#d8d8d8] bg-white shadow-[0_12px_28px_rgba(0,0,0,0.12)]"
      role="dialog"
      aria-label="Choose event location"
    >
      <div class="border-b border-[#eee] p-3">
        <label
          class="sr-only"
          :for="`${id}-search`"
        >Search location</label>
        <input
          :id="`${id}-search`"
          ref="searchInput"
          v-model="query"
          type="search"
          :placeholder="placeholder"
          autocomplete="off"
          class="box-border h-11 w-full rounded border border-solid border-[#b8b8b8] px-3 text-sm text-[#333] outline-none focus:border-brand-500"
          @input="onQueryInput"
        >
      </div>

      <div class="flex flex-col gap-1 p-2">
        <button
          type="button"
          class="flex items-center gap-2 rounded px-3 py-2.5 text-left text-sm text-[#333] transition-colors hover:bg-[#f7f7f7] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:opacity-60"
          :disabled="locating"
          @click="useCurrentLocation"
        >
          <span
            class="icon icon-location-1 text-brand-500"
            aria-hidden="true"
          />
          <span>{{ locating ? 'Detecting current location…' : 'Use Current Location' }}</span>
        </button>
        <button
          type="button"
          class="flex items-center gap-2 rounded px-3 py-2.5 text-left text-sm text-[#333] transition-colors hover:bg-[#f7f7f7] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          @click="useProgramLocation"
        >
          <span
            class="icon icon-address text-brand-500"
            aria-hidden="true"
          />
          <span>Program/Event Location</span>
        </button>
      </div>

      <ul
        v-if="suggestions.length"
        class="max-h-48 overflow-auto border-t border-[#eee]"
      >
        <li
          v-for="(item, index) in suggestions"
          :key="`${item.label}-${index}`"
        >
          <button
            type="button"
            class="w-full px-3 py-2.5 text-left text-sm text-[#333] hover:bg-[#f7f7f7] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
            @click="applyLocation(item.label)"
          >
            {{ item.label }}
          </button>
        </li>
      </ul>
      <p
        v-else-if="searching"
        class="border-t border-[#eee] px-3 py-2 text-xs text-[#777]"
      >
        Searching…
      </p>
      <p
        v-else-if="query.trim().length >= 2 && !suggestions.length"
        class="border-t border-[#eee] px-3 py-2 text-xs text-[#777]"
      >
        No matches — keep typing to use your text as the location.
      </p>

      <p
        v-if="statusMessage"
        class="border-t border-[#eee] px-3 py-2 text-xs text-[#a15c00]"
        role="status"
      >
        {{ statusMessage }}
      </p>
    </div>

    <input
      v-model="model"
      type="hidden"
      name="location"
      :required="required"
    >
  </div>
</template>
