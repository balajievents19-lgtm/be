<script setup lang="ts">
import type { Ref } from 'vue'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from '#imports'
import AppMegaMenu from '~/components/layout/AppMegaMenu.vue'
import AppMobileMenu from '~/components/layout/AppMobileMenu.vue'
import AppSearchPopup from '~/components/layout/AppSearchPopup.vue'
import type { HomePayload } from '~/types/home'
import type { NavLink } from '~/types/navigation'

const props = defineProps<{
  isScrolled: boolean
}>()

const route = useRoute()
const { data: settings } = useSettings()
const { header: menu } = useNavigation()
const home = inject<Ref<HomePayload | null> | null>('home', null)
const logoSrc = computed(
  () => settings.value?.brand?.logo || home?.value?.settings?.brand?.logo || '/images/logo.png'
)
const brandName = computed(() => settings.value?.company?.name?.trim() || 'Balaji Royal Events')
const headerCtaLabel = computed(() => settings.value?.header?.cta_label?.trim() || '')
const headerCtaUrl = computed(() => settings.value?.header?.cta_url?.trim() || '')
const showHeaderCta = computed(() => Boolean(headerCtaLabel.value && headerCtaUrl.value))
const headerCtaExternal = computed(() =>
  /^https?:\/\//i.test(headerCtaUrl.value)
  || headerCtaUrl.value.startsWith('mailto:')
  || headerCtaUrl.value.startsWith('tel:')
)
const isMegaMenuOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)
const servicesMenuRef = ref<HTMLElement | null>(null)

const setServicesMenuRef = (el: Element | null) => {
  servicesMenuRef.value = el as HTMLElement | null
}

const isActive = (item: NavLink) => {
  if (!item.to || item.external) {
    return false
  }

  if (item.to === '/') {
    return route.path === '/'
  }

  return route.path === item.to || route.path.startsWith(`${item.to}/`)
}

const navLinkClass = (item: NavLink, open = false) => [
  'relative flex items-center whitespace-nowrap border-t-4 px-[14px] text-base font-normal uppercase transition-all duration-1000 ease-in-out xl:px-[18px] xl:text-lg max-[991px]:px-3 max-[991px]:text-sm',
  'after:absolute after:top-0 after:left-1/2 after:ml-[-6px] after:hidden after:border-x-[6px] after:border-t-[6px] after:border-b-0 after:border-x-transparent after:border-t-brand-500 after:content-[\'\']',
  'hover:text-brand-500 hover:after:block',
  props.isScrolled ? 'py-5' : 'py-[28px]',
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
          :aria-label="`${brandName} home`"
        >
          <img
            :src="logoSrc"
            :alt="brandName"
            width="160"
            height="70"
            loading="eager"
            fetchpriority="high"
            decoding="async"
            class="block h-auto w-auto max-h-[64px] object-contain object-left transition-all duration-1000 ease-in-out"
            :class="props.isScrolled ? '!h-[48px] max-h-[48px]' : ''"
          >
        </NuxtLink>

        <ul class="m-0 hidden list-none items-stretch p-0 md:flex">
          <li
            v-for="item in menu"
            :key="item.id ?? `${item.label}-${item.to}`"
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

            <a
              v-else-if="item.external"
              :href="item.to"
              :target="item.target || '_blank'"
              :rel="item.target === '_blank' ? 'noopener noreferrer' : undefined"
              :class="navLinkClass(item)"
            >
              {{ item.label }}
            </a>

            <NuxtLink
              v-else
              :to="item.to"
              :class="navLinkClass(item)"
            >
              {{ item.label }}
            </NuxtLink>
          </li>
        </ul>

        <div class="hidden items-center gap-3 md:flex">
          <a
            v-if="showHeaderCta && headerCtaExternal"
            :href="headerCtaUrl"
            class="whitespace-nowrap rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-3 py-2 text-sm leading-none text-white transition-colors hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          >
            {{ headerCtaLabel }}
          </a>
          <NuxtLink
            v-else-if="showHeaderCta"
            :to="headerCtaUrl"
            class="whitespace-nowrap rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-3 py-2 text-sm leading-none text-white transition-colors hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          >
            {{ headerCtaLabel }}
          </NuxtLink>
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
