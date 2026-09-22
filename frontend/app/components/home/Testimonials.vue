<script setup lang="ts">
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Autoplay, Keyboard, Pagination } from 'swiper/modules'
import type { TestimonialItem } from '~/types/home'

import 'swiper/css'
import 'swiper/css/pagination'

const props = withDefaults(defineProps<{
  testimonials?: TestimonialItem[]
  successStories?: TestimonialItem[]
  pending?: boolean
  failed?: boolean
}>(), {
  testimonials: () => [],
  successStories: () => [],
  pending: false,
  failed: false
})

const { submitContact } = usePublicForms()

const modules = [A11y, Autoplay, Keyboard, Pagination]
const prefersReducedMotion = ref(false)
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

let mediaQuery: MediaQueryList | undefined

const updateReducedMotionPreference = () => {
  prefersReducedMotion.value = mediaQuery?.matches ?? false
}

onMounted(() => {
  mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
  updateReducedMotionPreference()
  mediaQuery.addEventListener('change', updateReducedMotionPreference)
})

onUnmounted(() => {
  mediaQuery?.removeEventListener('change', updateReducedMotionPreference)
})

const testimonialBreakpoints = {
  768: {
    slidesPerView: 2,
    slidesPerGroup: 2,
    spaceBetween: 20
  },
  1024: {
    slidesPerView: 3,
    slidesPerGroup: 3,
    spaceBetween: 24
  }
}

const autoplayOptions = computed(() => {
  if (prefersReducedMotion.value || props.testimonials.length < 2) {
    return false
  }

  return {
    delay: 4500,
    disableOnInteraction: false,
    pauseOnMouseEnter: true
  }
})

const shareForm = reactive({
  name: '',
  email: '',
  mobile: '',
  story: '',
  website: ''
})

const shareErrors = reactive({
  name: '',
  email: '',
  mobile: '',
  story: ''
})

const shareSubmitting = ref(false)
const shareSent = ref(false)
const shareApiError = ref('')

const hasTestimonials = computed(() => props.testimonials.length > 0)
const hasStories = computed(() => props.successStories.length > 0)

const onSwiper = (swiper: { slideNext: () => void, slidePrev: () => void }) => {
  swiperRef.value = swiper
}

const goNext = () => {
  swiperRef.value?.slideNext()
}

const goPrev = () => {
  swiperRef.value?.slidePrev()
}

const validateEmail = (value: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)

const onShareSubmit = async (event: Event) => {
  event.preventDefault()
  shareSent.value = false
  shareApiError.value = ''

  if (shareForm.website.trim()) {
    return
  }

  shareErrors.name = shareForm.name.trim() ? '' : 'Name cannot be blank.'
  shareErrors.mobile = shareForm.mobile.trim() ? '' : 'Phone cannot be blank.'
  shareErrors.email = shareForm.email.trim()
    ? (validateEmail(shareForm.email.trim()) ? '' : 'Incorrect e-mail address')
    : ''
  shareErrors.story = shareForm.story.trim() ? '' : 'Story cannot be blank.'

  if (shareErrors.name || shareErrors.mobile || shareErrors.email || shareErrors.story) {
    return
  }

  shareSubmitting.value = true
  try {
    await submitContact({
      name: shareForm.name.trim(),
      mobile: shareForm.mobile.trim(),
      email: shareForm.email.trim() || null,
      subject: 'Share Your Story',
      message: shareForm.story.trim(),
      website: ''
    })
    shareSent.value = true
    shareForm.name = ''
    shareForm.email = ''
    shareForm.mobile = ''
    shareForm.story = ''
    shareForm.website = ''
  } catch (error: unknown) {
    const err = error as { data?: { message?: string }, message?: string }
    shareApiError.value = err?.data?.message || err?.message || 'Unable to submit your story. Please try again.'
  } finally {
    shareSubmitting.value = false
  }
}
</script>

<template>
  <section
    class="friends-block relative block w-full bg-[url('/images/parallax/friend-infoBg.jpg')] bg-[length:cover] bg-[position:50%_50%] bg-fixed py-10 pb-16 max-md:bg-scroll min-[768px]:py-12 min-[768px]:pb-[72px]"
    aria-labelledby="client-says-heading"
  >
    <div
      class="pointer-events-none absolute inset-0 bg-[rgba(241,91,34,0.72)] content-['']"
      aria-hidden="true"
    />

    <UContainer class="relative z-[5] mx-auto max-w-[1170px] px-4 min-[768px]:px-8">
      <div class="sub-title block w-full text-center">
        <div class="block w-full text-center">
          <em
            class="icon icon-heading-icon mb-5 inline-block h-10 align-top text-[56px] leading-none text-white"
            aria-hidden="true"
          />
        </div>

        <h2
          id="client-says-heading"
          class="m-0 text-center text-[22px] font-bold leading-8 text-white font-['Domine',Georgia,'Times_New_Roman',serif] min-[768px]:text-2xl"
        >
          What Our Clients Say
        </h2>

        <div class="mt-[15px] block w-full text-center">
          <img
            src="/images/heading-blackBgimg.png"
            alt=""
            width="120"
            height="20"
            class="inline-block"
            loading="lazy"
            decoding="async"
            aria-hidden="true"
          >
        </div>
      </div>

      <p
        v-if="failed"
        class="m-0 mt-[21px] py-8 text-center text-sm text-white/80"
        role="alert"
      >
        Testimonials are temporarily unavailable.
      </p>
      <p
        v-else-if="pending && !hasTestimonials"
        class="m-0 mt-[21px] py-8 text-center text-sm text-white/80"
      >
        Loading testimonials…
      </p>
      <p
        v-else-if="!hasTestimonials"
        class="m-0 mt-[21px] py-8 text-center text-sm text-white/80"
      >
        No client stories to show yet.
      </p>

      <div
        v-else
        class="relative mt-[21px]"
      >
        <Swiper
          :modules="modules"
          :slides-per-view="1"
          :slides-per-group="1"
          :space-between="16"
          :speed="700"
          :auto-height="false"
          :watch-overflow="true"
          :rewind="true"
          :breakpoints="testimonialBreakpoints"
          :autoplay="autoplayOptions"
          :pagination="{ clickable: true }"
          :keyboard="{ enabled: true, onlyInViewport: true }"
          :a11y="{
            enabled: true,
            prevSlideMessage: 'Previous testimonials',
            nextSlideMessage: 'Next testimonials'
          }"
          class="testimonials-swiper w-full min-[768px]:px-10"
          @swiper="onSwiper"
        >
          <SwiperSlide
            v-for="item in testimonials"
            :key="item.id"
            class="!h-auto"
          >
            <article
              class="friends-info flex h-full min-h-full flex-col items-center px-1 pb-10 text-center"
            >
              <div class="friend-img relative w-full max-w-[168px]">
                <div class="relative mx-auto w-full max-w-[168px]">
                  <img
                    src="/images/img-fream.png"
                    alt=""
                    width="168"
                    height="168"
                    class="pointer-events-none relative z-[1] mx-auto block h-auto w-full max-w-[168px]"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                  >
                  <img
                    v-if="item.avatar || item.image"
                    :src="item.avatar || item.image || undefined"
                    :alt="item.name"
                    width="108"
                    height="108"
                    class="absolute top-1/2 left-1/2 z-0 h-[64%] w-[64%] max-h-[108px] max-w-[108px] -translate-x-1/2 -translate-y-1/2 rounded-full object-cover"
                    loading="lazy"
                    decoding="async"
                  >
                </div>
                <div
                  class="name px-2 pt-3 text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-base leading-6 text-white min-[768px]:text-lg min-[768px]:leading-[26px]"
                >
                  {{ item.name }}
                </div>
                <p
                  v-if="item.rating"
                  class="m-0 px-2 pt-1 pb-1 text-center text-sm leading-5 tracking-wide text-brand-500"
                  :aria-label="`${item.rating} out of 5`"
                >
                  {{ '★'.repeat(Math.min(5, Math.max(0, Math.round(item.rating)))) }}
                </p>
              </div>

              <div class="text mt-4 flex w-full min-h-[168px] flex-1 flex-col justify-start px-1">
                <p
                  class="m-0 max-w-full text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-[15px] leading-7 break-words text-white min-[768px]:text-base min-[768px]:leading-7"
                >
                  <img
                    src="/images/starting-point.png"
                    alt=""
                    width="24"
                    height="24"
                    class="start-img mr-0 inline-block h-[18px] w-auto pr-2 align-middle"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                  >
                  {{ item.quote || item.body }}
                  <img
                    src="/images/ending-point.png"
                    alt=""
                    width="24"
                    height="24"
                    class="end-img ml-0 inline-block h-[18px] w-auto pl-2 align-middle"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                  >
                </p>
              </div>
            </article>
          </SwiperSlide>
        </Swiper>

        <div class="mt-2 flex items-center justify-center gap-10 min-[768px]:pointer-events-none min-[768px]:absolute min-[768px]:inset-y-0 min-[768px]:mt-0 min-[768px]:block min-[768px]:w-full">
          <button
            type="button"
            class="z-[9] inline-flex h-10 w-10 items-center justify-center rounded-full text-white transition-colors hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white min-[768px]:pointer-events-auto min-[768px]:absolute min-[768px]:top-[42%] min-[768px]:left-0 min-[768px]:h-[27px] min-[768px]:w-[27px] min-[768px]:-translate-y-1/2"
            aria-label="Previous testimonials"
            @click="goPrev"
          >
            <span
              class="icon icon-arrow-left inline-block w-[27px] text-center text-xl leading-[27px]"
              aria-hidden="true"
            />
          </button>

          <button
            type="button"
            class="z-[9] inline-flex h-10 w-10 items-center justify-center rounded-full text-white transition-colors hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white min-[768px]:pointer-events-auto min-[768px]:absolute min-[768px]:top-[42%] min-[768px]:right-0 min-[768px]:h-[27px] min-[768px]:w-[27px] min-[768px]:-translate-y-1/2"
            aria-label="Next testimonials"
            @click="goNext"
          >
            <span
              class="icon icon-arrow-right inline-block w-[27px] text-center text-xl leading-[27px]"
              aria-hidden="true"
            />
          </button>
        </div>
      </div>
    </UContainer>
  </section>

  <!-- Success Story + Share Your Story -->
  <section
    class="story-block block bg-white py-[37px] pb-[76px]"
    aria-labelledby="success-story-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="-mx-[15px] flex flex-wrap">
        <div class="mb-8 w-full px-[15px] min-[768px]:mb-0 min-[768px]:w-2/3">
          <div>
            <div class="mb-[5px] overflow-hidden border-b border-solid border-[#d9d9d9]">
              <h2
                id="success-story-heading"
                class="m-0 inline text-lg leading-[38px] font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
              >
                Success Story
              </h2>
              <NuxtLink
                to="/about"
                class="float-right mt-2.5 text-xs leading-7 text-brand-500 transition-colors hover:text-black hover:[&_.icon]:text-black"
              >
                <span
                  class="icon icon-eye mr-[5px] inline-block align-middle text-sm leading-7 text-brand-500"
                  aria-hidden="true"
                />
                View All
              </NuxtLink>
            </div>

            <p
              v-if="!hasStories"
              class="m-0 mt-[27px] text-sm leading-6 text-[#888888]"
            >
              No success stories to show yet.
            </p>

            <article
              v-for="story in successStories"
              :key="story.id"
              class="relative mt-[27px] block p-0 pt-1.5 min-[768px]:pl-[100px] max-[479px]:p-0"
            >
              <div
                v-if="story.image"
                class="absolute top-0 left-[7px] w-[74px] overflow-hidden rounded-full border-2 border-solid border-[#c6c6c6] max-[479px]:static max-[479px]:mx-auto max-[479px]:mb-2.5"
              >
                <img
                  :src="story.image"
                  :alt="story.name"
                  class="block h-auto w-full"
                  width="370"
                  height="280"
                  loading="lazy"
                  decoding="async"
                >
              </div>

              <div>
                <p class="m-0 text-sm leading-6 text-[#888888]">
                  {{ story.body }}
                </p>
                <div class="block text-right text-sm leading-6 text-black italic">
                  - {{ story.name }}
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="w-full px-[15px] min-[768px]:w-1/3">
          <div>
            <h2
              class="mb-2 text-lg leading-[38px] font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
            >
              Share Your Story
            </h2>

            <form
              novalidate
              @submit="onShareSubmit"
            >
              <input
                v-model="shareForm.website"
                type="text"
                name="website"
                tabindex="-1"
                autocomplete="off"
                class="absolute left-[-10000px] h-px w-px overflow-hidden"
                aria-hidden="true"
              >

              <div class="mb-2.5">
                <label
                  class="sr-only"
                  for="share-name"
                >Name</label>
                <input
                  id="share-name"
                  v-model="shareForm.name"
                  type="text"
                  placeholder="Name"
                  class="h-10 w-full rounded-[5px] border border-solid border-[#cacbcb] bg-white px-2.5 py-2.5 text-sm leading-[18px] text-[#333] italic outline-none"
                  autocomplete="name"
                >
                <p
                  v-if="shareErrors.name"
                  class="mt-1 text-xs text-brand-600"
                >
                  {{ shareErrors.name }}
                </p>
              </div>

              <div class="mb-2.5">
                <label
                  class="sr-only"
                  for="share-mobile"
                >Phone</label>
                <input
                  id="share-mobile"
                  v-model="shareForm.mobile"
                  type="tel"
                  placeholder="Phone"
                  class="h-10 w-full rounded-[5px] border border-solid border-[#cacbcb] bg-white px-2.5 py-2.5 text-sm leading-[18px] text-[#333] italic outline-none"
                  autocomplete="tel"
                >
                <p
                  v-if="shareErrors.mobile"
                  class="mt-1 text-xs text-brand-600"
                >
                  {{ shareErrors.mobile }}
                </p>
              </div>

              <div class="mb-2.5">
                <label
                  class="sr-only"
                  for="share-email"
                >Email</label>
                <input
                  id="share-email"
                  v-model="shareForm.email"
                  type="email"
                  placeholder="Email"
                  class="h-10 w-full rounded-[5px] border border-solid border-[#cacbcb] bg-white px-2.5 py-2.5 text-sm leading-[18px] text-[#333] italic outline-none"
                  autocomplete="email"
                >
                <p
                  v-if="shareErrors.email"
                  class="mt-1 text-xs text-brand-600"
                >
                  {{ shareErrors.email }}
                </p>
              </div>

              <div class="mb-2.5">
                <label
                  class="sr-only"
                  for="share-story"
                >Your Story</label>
                <textarea
                  id="share-story"
                  v-model="shareForm.story"
                  placeholder="Your Story"
                  class="min-h-[96px] w-full resize-none rounded-[5px] border border-solid border-[#cacbcb] bg-white px-2.5 py-2.5 text-sm leading-[18px] text-[#333] italic outline-none"
                  rows="4"
                />
                <p
                  v-if="shareErrors.story"
                  class="mt-1 text-xs text-brand-600"
                >
                  {{ shareErrors.story }}
                </p>
              </div>

              <div>
                <button
                  type="submit"
                  :disabled="shareSubmitting"
                  class="w-full rounded-[3px] border border-solid border-brand-500 bg-brand-500 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:opacity-60"
                >
                  Share Your Story
                </button>
              </div>

              <p
                v-if="shareApiError"
                class="mt-2 text-xs text-brand-600"
                role="alert"
              >
                {{ shareApiError }}
              </p>
              <p
                v-if="shareSent"
                class="mt-2 text-xs text-[#6a6767]"
                role="status"
              >
                Thank you for sharing your story.
              </p>
            </form>
          </div>
        </div>
      </div>
    </UContainer>
  </section>
</template>

<style scoped>
.testimonials-swiper {
  padding-bottom: 2.75rem;
}

.testimonials-swiper :deep(.swiper-wrapper) {
  align-items: stretch;
}

.testimonials-swiper :deep(.swiper-slide) {
  display: flex;
  height: auto;
}

.testimonials-swiper :deep(.swiper-pagination) {
  bottom: 0;
}

.testimonials-swiper :deep(.swiper-pagination-bullet) {
  width: 9px;
  height: 9px;
  background: #ffffff;
  opacity: 0.4;
}

.testimonials-swiper :deep(.swiper-pagination-bullet-active) {
  opacity: 1;
  background: #ffffff;
}
</style>
