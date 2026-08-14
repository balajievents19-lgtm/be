<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import AppNavbar from '~/components/layout/AppNavbar.vue'
import AppTopBar from '~/components/layout/AppTopBar.vue'

const { data: settings } = useSettings()

const isScrolled = ref(false)
const isDesktop = ref(false)

const stickyEnabled = computed(() => settings.value?.header?.sticky_enabled !== false)
const topBarEnabled = computed(() => settings.value?.header?.top_bar_enabled !== false)

const updateScrollState = () => {
  if (!isDesktop.value || !stickyEnabled.value) {
    isScrolled.value = false
    return
  }

  isScrolled.value = window.scrollY > 0
}

const updateViewport = () => {
  isDesktop.value = window.matchMedia('(min-width: 768px)').matches
  updateScrollState()
}

onMounted(() => {
  updateViewport()
  window.addEventListener('scroll', updateScrollState, { passive: true })
  window.addEventListener('resize', updateViewport, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateScrollState)
  window.removeEventListener('resize', updateViewport)
})
</script>

<template>
  <header
    id="header"
    class="relative z-50 w-full md:absolute md:left-0 md:top-0"
    :class="{ 'md:fixed': isScrolled && stickyEnabled }"
  >
    <AppTopBar v-if="topBarEnabled" />
    <AppNavbar :is-scrolled="isScrolled" />
  </header>
</template>
