/**
 * Swiper mutates slide DOM on the client. Mount it after hydrate so SSR markup stays stable.
 */
export const useSwiperAfterHydrate = () => {
  const active = ref(false)

  onMounted(() => {
    active.value = true
  })

  return active
}
