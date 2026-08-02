<script setup lang="ts">
import { nextTick, ref, watch } from 'vue'
import { useRouter } from '#imports'

defineProps<{
  id?: string
}>()

const open = defineModel<boolean>('open', { default: false })
const query = ref('')
const inputRef = ref<{ $el?: HTMLElement } | null>(null)
const router = useRouter()

const focusInput = async () => {
  await nextTick()
  const root = inputRef.value?.$el
  const input = root?.querySelector?.('input') as HTMLInputElement | null
  input?.focus()
}

watch(open, (isOpen) => {
  if (isOpen) {
    focusInput()
  }
})

const close = () => {
  open.value = false
}

const submit = async () => {
  const search = query.value.trim()
  close()
  await router.push({
    path: '/services',
    query: search ? { search } : undefined
  })
}
</script>

<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      :id="id"
      class="absolute inset-x-0 top-0 z-30 hidden h-full md:block"
      role="search"
      @keydown.esc="close"
    >
      <div
        class="absolute inset-0"
        aria-hidden="true"
        @click="close"
      />

      <UContainer class="relative z-10 mx-auto flex h-full max-w-[1170px] items-center pt-[25px]">
        <form
          class="ml-auto w-full max-w-[790px] bg-brand-500 p-px"
          @submit.prevent="submit"
          @click.stop
        >
          <div class="relative flex">
            <UInput
              ref="inputRef"
              v-model="query"
              placeholder="Search here"
              aria-label="Search here"
              class="min-w-0 flex-1"
              :ui="{
                base: 'h-[38px] rounded-none border-0 bg-white px-[10px] py-[5px] text-[13px] italic text-[#666] focus-visible:ring-0'
              }"
            />
            <button
              type="submit"
              class="absolute right-0 top-0 flex h-[38px] w-[38px] items-center justify-center text-white transition-colors hover:text-white/80"
              aria-label="Search"
            >
              <span
                class="icon icon-search text-base"
                aria-hidden="true"
              />
            </button>
          </div>
        </form>
      </UContainer>
    </div>
  </Transition>
</template>
