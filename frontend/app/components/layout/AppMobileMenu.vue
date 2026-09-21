<script setup lang="ts">
import type { Ref } from 'vue'
import { ref, watch } from 'vue'
import { useRoute, useRouter } from '#imports'
import { useServices } from '~/composables/useServices'
import type { HomePayload } from '~/types/home'
import type { NavLink } from '~/types/navigation'

const props = defineProps<{
  id?: string
  items: NavLink[]
}>()

const open = defineModel<boolean>('open', { default: false })
const servicesOpen = ref(false)
const searchQuery = ref('')
const route = useRoute()
const router = useRouter()
const { data: services } = useServices({ server: false })
const { data: settings } = useSettings()
const { customer, openLogin, openRegister, logout } = useCustomerAuth()
const home = inject<Ref<HomePayload | null> | null>('home', null)
const logoSrc = computed(
  () => settings.value?.brand?.logo || home?.value?.settings?.brand?.logo || '/images/logo.png'
)
const brandName = computed(() => settings.value?.company?.name?.trim() || 'Balaji Royal Events')

const isActive = (item: NavLink) => {
  if (!item.to || item.external) {
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

const onLogin = () => {
  close()
  openLogin()
}

const onRegister = () => {
  close()
  openRegister()
}

const onLogout = async () => {
  close()
  await logout()
  await navigateTo('/')
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
            :aria-label="`${brandName} home`"
            @click="close"
          >
            <img
              :src="logoSrc"
              :alt="brandName"
              width="140"
              height="56"
              loading="lazy"
              decoding="async"
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
            :key="item.id ?? `${item.label}-${item.to}`"
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
                  :key="service.id"
                  :to="`/services/${service.slug}`"
                  class="block px-4 py-3 text-base text-[#202020] transition-colors hover:text-brand-500"
                  @click="close"
                >
                  {{ service.name }}
                </NuxtLink>
              </div>
            </div>

            <a
              v-else-if="item.external"
              :href="item.to"
              :target="item.target || '_blank'"
              :rel="item.target === '_blank' ? 'noopener noreferrer' : undefined"
              class="block border-b border-[#f0f0f0] py-4 text-lg font-normal uppercase transition-colors text-[#202020] hover:text-brand-500"
              @click="close"
            >
              {{ item.label }}
            </a>

            <NuxtLink
              v-else
              :to="item.to"
              class="block border-b border-[#f0f0f0] py-4 text-lg font-normal uppercase transition-colors"
              :class="isActive(item) ? 'text-brand-500' : 'text-[#202020] hover:text-brand-500'"
              @click="close"
            >
              {{ item.label }}
            </NuxtLink>
          </template>

          <div class="mt-6 border-t border-[#f0f0f0] pt-4">
            <p class="mb-3 text-xs font-semibold tracking-wide text-[#888] uppercase">
              Account
            </p>
            <template v-if="customer">
              <NuxtLink
                to="/account"
                class="block border-b border-[#f0f0f0] py-4 text-lg font-normal uppercase text-[#202020] transition-colors hover:text-brand-500"
                @click="close"
              >
                Account
              </NuxtLink>
              <button
                type="button"
                class="block w-full border-b border-[#f0f0f0] py-4 text-left text-lg font-normal uppercase text-[#202020] transition-colors hover:text-brand-500"
                @click="onLogout"
              >
                Logout
              </button>
            </template>
            <template v-else>
              <button
                type="button"
                class="block w-full border-b border-[#f0f0f0] py-4 text-left text-lg font-normal uppercase text-[#202020] transition-colors hover:text-brand-500"
                @click="onLogin"
              >
                Login
              </button>
              <button
                type="button"
                class="block w-full border-b border-[#f0f0f0] py-4 text-left text-lg font-normal uppercase text-[#202020] transition-colors hover:text-brand-500"
                @click="onRegister"
              >
                Registration
              </button>
            </template>
          </div>
        </nav>
      </div>
    </template>
  </USlideover>
</template>
