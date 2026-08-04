<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Keyboard, Navigation } from 'swiper/modules'
import type { TestimonialItem } from '~/types/home'

import 'swiper/css'
import 'swiper/css/navigation'

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

const modules = [A11y, Keyboard, Navigation]
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

const shareForm = reactive({
  name: '',
  email: '',
  mobile: '',
  photoName: '',
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
const fileInputRef = ref<HTMLInputElement | null>(null)

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

const onPhotoChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  shareForm.photoName = file?.name ?? ''
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
    shareForm.photoName = ''
    shareForm.story = ''
    shareForm.website = ''
    if (fileInputRef.value) {
      fileInputRef.value.value = ''
    }
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
    class="friends-block relative block w-full bg-[url('/images/parallax/friend-infoBg.jpg')] bg-[length:cover] bg-[position:50%_50%] bg-fixed py-[33px] pb-[63px] max-md:bg-scroll"
    aria-labelledby="client-says-heading"
  >
    <div
      class="pointer-events-none absolute inset-0 bg-[var(--color-primary-overlay)] content-['']"
      aria-hidden="true"
    />

    <UContainer class="relative z-[5] mx-auto max-w-[1170px]">
      <div class="sub-title block w-full text-center">
        <div class="block w-full text-center">
          <em
            class="icon icon-heading-icon mb-5 inline-block h-10 align-top text-[56px] leading-none text-white"
            aria-hidden="true"
          />
        </div>

        <h2
          id="client-says-heading"
          class="m-0 text-center text-2xl font-bold leading-8 text-white font-['Domine',Georgia,'Times_New_Roman',serif]"
        >
          Client Say’s
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
          :speed="600"
          :keyboard="{ enabled: true }"
          :a11y="{ enabled: true }"
          class="w-full"
          @swiper="onSwiper"
        >
          <SwiperSlide
            v-for="item in testimonials"
            :key="item.id"
          >
            <div
              class="friends-info relative mt-0 block min-h-0 p-0 min-[768px]:min-h-[315px] min-[768px]:pl-[315px]"
            >
              <div class="friend-img relative w-full text-center min-[768px]:absolute min-[768px]:top-0 min-[768px]:left-0 min-[768px]:m-0 min-[768px]:w-auto">
                <div class="relative inline-block w-[278px] overflow-hidden px-[50px] py-[55px]">
                  <img
                    v-if="item.avatar"
                    :src="item.avatar"
                    :alt="item.name"
                    width="178"
                    height="178"
                    class="h-[178px] w-[178px] rounded-full object-cover"
                    loading="lazy"
                    decoding="async"
                  >
                </div>
                <div class="img-fream pointer-events-none absolute top-0 left-0 w-full text-center">
                  <img
                    src="/images/img-fream.png"
                    alt=""
                    width="278"
                    height="278"
                    class="inline-block w-auto max-w-none"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                  >
                </div>
                <div
                  class="name pt-[11px] text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-lg leading-[26px] text-white min-[768px]:pt-[5px]"
                >
                  {{ item.name }}
                </div>
              </div>

              <div class="text pt-[30px] min-[768px]:pt-[68px]">
                <p
                  class="m-0 text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-lg leading-8 text-white min-[992px]:text-xl min-[992px]:leading-[42px]"
                >
                  <img
                    src="/images/starting-point.png"
                    alt=""
                    width="24"
                    height="24"
                    class="start-img mr-0 inline-block w-auto pr-2.5 align-middle"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                  >
                  {{ item.quote }}
                  <img
                    src="/images/ending-point.png"
                    alt=""
                    width="24"
                    height="24"
                    class="end-img ml-0 inline-block w-auto pl-2.5 align-middle"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                  >
                </p>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>

        <button
          type="button"
          class="absolute top-1/2 left-[-6%] z-[9] mt-[-13px] h-[27px] w-[27px] rounded-full text-white transition-colors hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-[1399px]:left-[-20px] max-[991px]:left-[-10px]"
          aria-label="Previous testimonial"
          @click="goPrev"
        >
          <span
            class="icon icon-arrow-left inline-block w-[27px] text-center text-xl leading-[27px]"
            aria-hidden="true"
          />
        </button>

        <button
          type="button"
          class="absolute top-1/2 right-[-6%] z-[9] mt-[-13px] h-[27px] w-[27px] rounded-full text-white transition-colors hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-[1399px]:right-[-20px] max-[991px]:right-[-10px]"
          aria-label="Next testimonial"
          @click="goNext"
        >
          <span
            class="icon icon-arrow-right inline-block w-[27px] text-center text-xl leading-[27px]"
            aria-hidden="true"
          />
        </button>
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
            <h2
              id="success-story-heading"
              class="mb-[5px] overflow-hidden border-b border-solid border-[#d9d9d9] text-lg leading-[38px] font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
            >
              Success Story
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
            </h2>

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

              <div class="relative mb-2.5">
                <label
                  class="sr-only"
                  for="share-photo"
                >Your Photo</label>
                <input
                  id="share-photo"
                  ref="fileInputRef"
                  type="file"
                  accept="image/*"
                  class="relative z-[2] h-10 w-full opacity-0"
                  @change="onPhotoChange"
                >
                <div class="pointer-events-none absolute top-0 left-0 z-[1] h-10 w-full">
                  <input
                    type="text"
                    :value="shareForm.photoName"
                    placeholder="Your Photo"
                    tabindex="-1"
                    readonly
                    aria-hidden="true"
                    class="h-10 w-full rounded-[5px] border border-solid border-[#cacbcb] bg-white px-2.5 py-2.5 text-sm leading-[18px] text-[#333] italic"
                  >
                </div>
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
