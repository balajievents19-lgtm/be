<script setup lang="ts">
import AppMobileMenu from '~/components/layout/AppMobileMenu.vue'
const route = useRoute()

const mobileMenuOpen = ref(false)
const isScrolled = ref(false)
const servicesOpen = ref(false)

const menu = [
  { label: 'Home', to: '/' },
  { label: 'About', to: '/about' },
  {
    label: 'Services',
    children: true
  },
  { label: 'FAQ', to: '/faq' },
  { label: 'Contact', to: '/contact' }
]

const handleScroll = () => {
  isScrolled.value = window.scrollY > 40
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

watch(
  () => route.fullPath,
  () => {
    mobileMenuOpen.value = false
    servicesOpen.value = false
  }
)
</script>

<template>
  <nav
  <AppMobileMenu
  :open="mobileMenuOpen"
  @close="mobileMenuOpen = false"
/>
    class="fixed inset-x-0 top-10 z-40 transition-all duration-300"
    :class="[
      isScrolled
        ? 'bg-white shadow-xl py-3'
        : 'bg-white py-5'
    ]"
  >
    <UContainer class="max-w-[1170px]">

      <div class="flex items-center justify-between">

        <!-- Logo -->
        <NuxtLink
          to="/"
          class="shrink-0"
        >
          <img
            src="/images/logo.png"
            alt="Balaji Events"
            class="h-16 w-auto object-contain lg:h-20"
          >
        </NuxtLink>

        <!-- Desktop Menu -->
        <ul
          class="hidden items-center gap-10 lg:flex"
        >
          <li
            v-for="item in menu"
            :key="item.label"
            class="relative"
          >

            <!-- Normal Menu -->
            <NuxtLink
              v-if="!item.children"
              :to="item.to"
              class="font-medium uppercase tracking-wide transition-all duration-300"
              :class="
                route.path === item.to
                  ? 'text-[#f85a1f]'
                  : 'text-slate-800 hover:text-[#f85a1f]'
              "
            >
              {{ item.label }}
            </NuxtLink>

            <!-- Services -->
            <button
              v-else
              class="flex items-center gap-1 font-medium uppercase tracking-wide text-slate-800 transition hover:text-[#f85a1f]"
              @mouseenter="servicesOpen = true"
              @mouseleave="servicesOpen = false"
            >
              Services

              <UIcon
                name="i-lucide-chevron-down"
                class="size-4"
              />
            </button>

          </li>
        </ul>

        <!-- Right -->
        <div class="hidden items-center gap-4 lg:flex">

          <UButton
            icon="i-lucide-search"
            color="neutral"
            variant="ghost"
            square
          />

          <UButton
            to="/contact"
            color="primary"
            size="lg"
          >
            Book Now
          </UButton>

        </div>

        <!-- Mobile -->
        <UButton
          class="lg:hidden"
          square
          color="neutral"
          variant="ghost"
          :icon="mobileMenuOpen ? 'i-lucide-x' : 'i-lucide-menu'"
          @click="mobileMenuOpen = !mobileMenuOpen"
        />

      </div>

      <!-- Mega Menu -->
      <Transition
        enter-active-class="transition duration-300"
        leave-active-class="transition duration-200"
        enter-from-class="opacity-0 translate-y-2"
        leave-to-class="opacity-0 translate-y-2"
      >
        <AppMegaMenu
          v-if="servicesOpen"
          @mouseenter="servicesOpen=true"
          @mouseleave="servicesOpen=false"
        />
      </Transition>

    </UContainer>
  </nav>
</template>