<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from '#imports'
import AppMegaMenu from './AppMegaMenu.vue'


const route = useRoute()

const mobileMenuOpen = ref(false)
const servicesOpen = ref(false)
const isScrolled = ref(false)

const menu = [
  {
    label: 'Home',
    to: '/'
  },
  {
    label: 'About',
    to: '/about'
  },
  {
    label: 'Services',
    children: true
  },
  {
    label: 'Gallery',
    to: '/gallery'
  },
  {
    label: 'FAQ',
    to: '/faq'
  },
  {
    label: 'Contact',
    to: '/contact'
  }
]

const handleScroll = () => {
  isScrolled.value = window.scrollY > 40
}

onMounted(() => {
  handleScroll()
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
  <header
    class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
    :class="[
      isScrolled
        ? 'bg-white shadow-xl'
        : 'bg-white/95 backdrop-blur'
    ]"
  >
    <UContainer class="max-w-7xl">

      <div
        class="flex h-20 items-center justify-between"
      >

        <!-- Logo -->
        <NuxtLink
          to="/"
          class="flex items-center shrink-0"
        >
          <img
            src="/images/logo.png"
            alt="Balaji Events"
            class="h-14 w-auto lg:h-16"
          />
        </NuxtLink>

        <!-- Desktop Menu -->
        <ul
          class="hidden lg:flex items-center gap-10"
        >
          <li
            v-for="item in menu"
            :key="item.label"
            class="relative"
          >

            <NuxtLink
              v-if="!item.children"
              :to="item.to"
              class="font-semibold uppercase tracking-wide transition-colors duration-300"
              :class="
                route.path === item.to
                  ? 'text-primary'
                  : 'text-gray-800 hover:text-primary'
              "
            >
              {{ item.label }}
            </NuxtLink>

            <div
              v-else
              class="relative"
              @mouseenter="servicesOpen = true"
              @mouseleave="servicesOpen = false"
            >
              <button
                class="flex items-center gap-1 font-semibold uppercase text-gray-800 hover:text-primary transition"
              >
                Services

                <UIcon
                  name="i-lucide-chevron-down"
                  class="size-4"
                />
              </button>
                            <!-- Mega Menu -->
              <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
              >
                <AppMegaMenu
                 v-if="servicesOpen"
  @mouseenter="servicesOpen = true"
  @mouseleave="servicesOpen = false"
                />
              </Transition>

            </div>

          </li>

        </ul>

        <!-- Right Side -->
        <div
          class="hidden lg:flex items-center gap-3"
        >

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
            class="font-semibold"
          >
            Book Now
          </UButton>

        </div>

        <!-- Mobile Toggle -->
        <UButton
          class="lg:hidden"
          color="neutral"
          variant="ghost"
          square
          :icon="
            mobileMenuOpen
              ? 'i-lucide-x'
              : 'i-lucide-menu'
          "
          @click="mobileMenuOpen = !mobileMenuOpen"
        />

      </div>

    </UContainer>

      </header>

  <!-- Mobile Menu 
  <AppMobileMenu
    :open="mobileMenuOpen"
    :menu="menu"
    @close="mobileMenuOpen = false"
  />-->
</template>