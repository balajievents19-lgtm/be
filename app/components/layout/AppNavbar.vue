<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from '#imports'
import AppMegaMenu from '~/components/layout/AppMegaMenu.vue'
import AppMobileMenu from '~/components/layout/AppMobileMenu.vue'
import AppSearchPopup from '~/components/layout/AppSearchPopup.vue'

export interface NavigationItem {
  label: string
  to?: string
  children?: boolean
}

const props = defineProps<{
  isScrolled: boolean
}>()

const route = useRoute()
const isMegaMenuOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)
const servicesMenuRef = ref<HTMLElement | null>(null)

const setServicesMenuRef = (el: Element | null) => {
  servicesMenuRef.value = el as HTMLElement | null
}

const menu: NavigationItem[] = [
  { label: 'Home', to: '/' },
  { label: 'About Us', to: '/about' },
  { label: 'Services', to: '/services', children: true },
  { label: 'FAQ’s', to: '/faq' },
  { label: 'Contact us', to: '/contact' }
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

const navLinkClass = (item: NavigationItem, open = false) => [
  'relative flex items-center border-t-4 px-[18px] text-lg font-normal uppercase transition-all duration-1000 ease-in-out max-[991px]:px-3 max-[991px]:text-sm',
  'after:absolute after:top-0 after:left-1/2 after:ml-[-6px] after:hidden after:border-x-[6px] after:border-t-[6px] after:border-b-0 after:border-x-transparent after:border-t-brand-500 after:content-[\'\']',
  'hover:text-brand-500 hover:after:block',
  props.isScrolled ? 'py-5' : 'py-[30px]',
  isActive(item) || open
    ? 'border-brand-500 text-brand-500 after:block'
    : 'border-white text-[#202020]'
]

const closeMenus = () => {
  isMegaMenuOpen.value = false
  isSearchOpen.value = false
}

const closeAll = () => {
  closeMenus()
  isMobileMenuOpen.value = false
}

const openSearch = () => {
  isMegaMenuOpen.value = false
  isSearchOpen.value = true
}

const onDocumentClick = (event: MouseEvent) => {
  const target = event.target as Node | null
  if (!target) {
    return
  }

  if (servicesMenuRef.value?.contains(target)) {
    return
  }

  isMegaMenuOpen.value = false
}

const onKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closeMenus()
  }
}

watch(
  () => route.fullPath,
  closeAll
)

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
  document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  document.removeEventListener('click', onDocumentClick)
  document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <nav
    id="nav-main"
    class="relative z-50 bg-white shadow-[0_3px_5px_rgba(16,15,15,0.16)]"
    aria-label="Main navigation"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="relative flex items-center justify-between">
        <NuxtLink
          to="/"
          class="shrink-0 transition-all duration-1000 ease-in-out"
          :class="props.isScrolled ? 'my-[10px]' : 'my-[15px] md:mt-[15px] md:mb-0'"
          aria-label="Balaji Events home"
        >
          <img
            src="/images/logo.png"
            alt="Balaji Events"
            width="160"
            height="70"
            decoding="async"
            class="h-auto w-auto max-h-[70px] transition-all duration-1000 ease-in-out"
            :class="props.isScrolled ? '!h-[50px] max-h-[50px]' : ''"
          >
        </NuxtLink>

        <ul class="m-0 hidden list-none items-stretch p-0 md:flex">
          <li
            v-for="item in menu"
            :key="item.label"
            class="relative flex items-stretch"
          >
            <div
              v-if="item.children"
              :ref="setServicesMenuRef"
              class="relative flex items-stretch"
              @mouseenter="isMegaMenuOpen = true"
              @mouseleave="isMegaMenuOpen = false"
            >
              <NuxtLink
                :to="item.to"
                class="gap-[5px]"
                :class="navLinkClass(item, isMegaMenuOpen)"
                :aria-expanded="isMegaMenuOpen"
                aria-controls="services-menu"
                aria-haspopup="true"
                @focus="isMegaMenuOpen = true"
              >
                {{ item.label }}
                <span
                  class="icon icon-arrow-down text-xs"
                  aria-hidden="true"
                />
              </NuxtLink>

              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-1 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-1 opacity-0"
              >
                <AppMegaMenu
                  v-if="isMegaMenuOpen"
                  id="services-menu"
                />
              </Transition>
            </div>

            <NuxtLink
              v-else-if="item.to"
              :to="item.to"
              :class="navLinkClass(item)"
            >
              {{ item.label }}
            </NuxtLink>
          </li>
        </ul>

        <div class="hidden items-center md:flex">
          <button
            type="button"
            class="flex size-9 items-center justify-center text-black transition-colors hover:text-brand-500"
            :aria-expanded="isSearchOpen"
            aria-controls="header-search"
            aria-label="Open search"
            @click.stop="openSearch"
          >
            <span
              class="icon icon-search text-base"
              aria-hidden="true"
            />
          </button>
        </div>

        <button
          type="button"
          class="flex flex-col justify-center gap-1 px-[10px] py-[9px] md:hidden"
          :aria-expanded="isMobileMenuOpen"
          aria-controls="mobile-navigation"
          :aria-label="isMobileMenuOpen ? 'Close navigation menu' : 'Open navigation menu'"
          @click="isMobileMenuOpen = !isMobileMenuOpen"
        >
          <span
            class="block h-1 w-[35px] rounded-sm bg-brand-500"
            aria-hidden="true"
          />
          <span
            class="block h-1 w-[35px] rounded-sm bg-brand-500"
            aria-hidden="true"
          />
          <span
            class="block h-1 w-[35px] rounded-sm bg-brand-500"
            aria-hidden="true"
          />
        </button>
      </div>
    </UContainer>

    <AppSearchPopup
      id="header-search"
      v-model:open="isSearchOpen"
    />
  </nav>

  <AppMobileMenu
    id="mobile-navigation"
    v-model:open="isMobileMenuOpen"
    :items="menu"
  />
</template>
