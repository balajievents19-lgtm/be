<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from '#imports'

interface NavigationItem {
  label: string
  to?: string
  children?: boolean
}

const props = defineProps<{
  id?: string
  items: NavigationItem[]
}>()

const open = defineModel<boolean>('open', { default: false })
const servicesOpen = ref(false)
const searchQuery = ref('')
const route = useRoute()
const router = useRouter()

const services = [
  'Caterers',
  'Mehndi',
  'Decor & Florists',
  'Cakes',
  'Wedding Planner',
  'Gifts and Flowers',
  'Make-up and Hair',
  'Entertainment',
  'Photographers/ Videographers',
  'DJ',
  'Wedding Cards'
]

const isActive = (item: NavigationItem) => {
  if (!item.to) {
    return false
  }

  if (item.to === '/') {
    return route.path === '/'
  }

  return route.path === item.to || route.path.startsWith(`${item.to}/`)
}

const close = () => {
  open.value = false
}

watch(open, (isOpen) => {
  if (!isOpen) {
    servicesOpen.value = false
  }
})

const submitSearch = async () => {
  const search = searchQuery.value.trim()
  await router.push({
    path: '/services',
    query: search ? { search } : undefined
  })
  close()
}
</script>

<template>
  <USlideover
    v-model:open="open"
    side="right"
    :ui="{ content: 'max-w-sm' }"
  >
    <template #content>
      <div
        :id="props.id"
        class="flex h-full flex-col bg-white"
      >
        <div class="flex items-center justify-between border-b border-[#e5e5e5] px-5 py-4">
          <NuxtLink
            to="/"
            aria-label="Balaji Events home"
            @click="close"
          >
            <img
              src="/images/logo.png"
              alt="Balaji Events"
              class="h-14 w-auto"
            >
          </NuxtLink>

          <button
            type="button"
            class="flex size-10 items-center justify-center text-[#202020] transition-colors hover:text-brand-500"
            aria-label="Close navigation menu"
            @click="close"
          >
            <UIcon
              name="i-lucide-x"
              class="size-5"
            />
          </button>
        </div>

        <nav
          class="flex-1 overflow-y-auto px-5 py-4"
          aria-label="Mobile navigation"
        >
          <form
            class="mb-5 bg-brand-500 p-px"
            role="search"
            @submit.prevent="submitSearch"
          >
            <div class="relative flex">
              <UInput
                v-model="searchQuery"
                placeholder="Search here"
                aria-label="Search here"
                class="min-w-0 flex-1"
                :ui="{
                  base: 'h-10 rounded-none border-0 bg-white px-3 text-sm italic text-[#666] focus-visible:ring-0'
                }"
              />
              <button
                type="submit"
                class="absolute right-0 top-0 flex h-10 w-10 items-center justify-center text-white"
                aria-label="Search"
              >
                <span
                  class="icon icon-search text-base"
                  aria-hidden="true"
                />
              </button>
            </div>
          </form>

          <template
            v-for="item in props.items"
            :key="item.label"
          >
            <div
              v-if="item.children"
              class="border-b border-[#f0f0f0]"
            >
              <button
                type="button"
                class="flex w-full items-center justify-between py-4 text-lg font-normal uppercase transition-colors"
                :class="isActive(item) ? 'text-brand-500' : 'text-[#202020] hover:text-brand-500'"
                :aria-expanded="servicesOpen"
                aria-controls="mobile-services-menu"
                @click="servicesOpen = !servicesOpen"
              >
                <span>{{ item.label }}</span>
                <span
                  class="icon icon-arrow-down text-xs transition-transform"
                  :class="{ 'rotate-180': servicesOpen }"
                  aria-hidden="true"
                />
              </button>

              <div
                v-show="servicesOpen"
                id="mobile-services-menu"
                class="pb-3"
              >
                <NuxtLink
                  v-for="service in services"
                  :key="service"
                  to="/services"
                  class="block px-4 py-3 text-base text-[#202020] transition-colors hover:text-brand-500"
                  @click="close"
                >
                  {{ service }}
                </NuxtLink>
              </div>
            </div>

            <NuxtLink
              v-else-if="item.to"
              :to="item.to"
              class="block border-b border-[#f0f0f0] py-4 text-lg font-normal uppercase transition-colors"
              :class="isActive(item) ? 'text-brand-500' : 'text-[#202020] hover:text-brand-500'"
              @click="close"
            >
              {{ item.label }}
            </NuxtLink>
          </template>
        </nav>
      </div>
    </template>
  </USlideover>
</template>
