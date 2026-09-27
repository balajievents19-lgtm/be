/**
 * Client-only widgets (Swiper, Reka/Nuxt UI popover & modal) mutate or randomize DOM.
 * Mount them after hydrate so SSR markup matches the first client paint.
 */
export const useSwiperAfterHydrate = () => {
  const active = ref(false)

  onMounted(() => {
    active.value = true
  })

  return active
}
