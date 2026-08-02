<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { A11y, Keyboard, Navigation } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/navigation'

interface Testimonial {
  name: string
  quote: string
  avatar: string
}

interface Story {
  name: string
  image: string
  text: string
}

const modules = [A11y, Keyboard, Navigation]
const swiperRef = ref<{ slideNext: () => void, slidePrev: () => void } | null>(null)

const testimonials: Testimonial[] = [
  {
    name: 'John Doe',
    avatar: '/images/user/friend-img.png',
    quote: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, an unknown printer took a galley of type and scrambled it type specimen book.'
  },
  {
    name: 'John Doe',
    avatar: '/images/user/friend-img.png',
    quote: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, an unknown printer took a galley of type and scrambled it type specimen book.'
  }
]

const stories: Story[] = [
  {
    name: 'Larry Cook',
    image: '/images/user/story-img1.png',
    text: 'We have 20 years experience planning and organizing beautiful weddings and events. We have built up excellent relationships with the most professional suppliers on the coast to help with your every desire. From a small intimate gathering to a more luxurious wedding day we can help you make your Dream Wedding a reality.'
  },
  {
    name: 'Stacy Benjamin',
    image: '/images/user/story-img2.png',
    text: 'We have 20 years experience planning and organizing beautiful weddings and events. We have built up excellent relationships with the most professional suppliers on the coast to help with your every desire. From a small intimate gathering to a more luxurious wedding day we can help you make your Dream Wedding a reality.'
  }
]

const shareForm = reactive({
  name: '',
  email: '',
  photoName: '',
  story: ''
})

const shareErrors = reactive({
  name: '',
  email: '',
  story: ''
})

const fileInputRef = ref<HTMLInputElement | null>(null)

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

const onShareSubmit = (event: Event) => {
  event.preventDefault()

  shareErrors.name = shareForm.name.trim() ? '' : 'Name cannot be blank.'
  shareErrors.email = shareForm.email.trim()
    ? (validateEmail(shareForm.email.trim()) ? '' : 'Incorrect e-mail address')
    : 'Incorrect e-mail address'
  shareErrors.story = shareForm.story.trim() ? '' : 'Story cannot be blank.'

  if (shareErrors.name || shareErrors.email || shareErrors.story) {
    return
  }

  shareForm.name = ''
  shareForm.email = ''
  shareForm.photoName = ''
  shareForm.story = ''
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}
</script>

<template>
  <!-- Client Say’s -->
  <section
    class="relative block w-full bg-[url('/images/parallax/friend-infoBg.jpg')] bg-cover bg-center bg-fixed py-[33px] pb-[63px]"
    aria-labelledby="client-says-heading"
  >
    <div
      class="pointer-events-none absolute inset-0 bg-[rgba(241,91,34,0.9)]"
      aria-hidden="true"
    />

    <UContainer class="relative z-[5] mx-auto max-w-[1170px]">
      <div class="block w-full text-center">
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
            class="inline-block"
            aria-hidden="true"
          >
        </div>
      </div>

      <div class="relative mt-[21px]">
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
            v-for="(item, index) in testimonials"
            :key="`testimonial-${index}`"
          >
            <div
              class="relative mt-0 block min-h-0 p-0 md:min-h-[315px] md:pl-[315px]"
            >
              <div class="relative w-full text-center md:absolute md:top-0 md:left-0 md:w-auto md:text-left">
                <div class="relative inline-block w-[278px] overflow-hidden px-[50px] py-[55px]">
                  <img
                    :src="item.avatar"
                    :alt="item.name"
                    class="h-[178px] w-[178px] rounded-full object-cover"
                    loading="lazy"
                  >
                </div>
                <div class="pointer-events-none absolute top-0 left-0 w-full text-center">
                  <img
                    src="/images/img-fream.png"
                    alt=""
                    class="inline-block w-auto max-w-none"
                    aria-hidden="true"
                  >
                </div>
                <div
                  class="pt-[11px] text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-lg leading-[26px] text-white md:pt-[5px]"
                >
                  {{ item.name }}
                </div>
              </div>

              <div class="pt-[30px] md:pt-[68px]">
                <p
                  class="m-0 text-center font-['Domine',Georgia,'Times_New_Roman',serif] text-lg leading-8 text-white min-[992px]:text-xl min-[992px]:leading-[42px]"
                >
                  <img
                    src="/images/starting-point.png"
                    alt=""
                    class="mr-2.5 inline-block w-auto align-middle"
                    aria-hidden="true"
                  >
                  {{ item.quote }}
                  <img
                    src="/images/ending-point.png"
                    alt=""
                    class="ml-2.5 inline-block w-auto align-middle"
                    aria-hidden="true"
                  >
                </p>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>

        <button
          type="button"
          class="absolute top-1/2 left-[-10px] z-[9] mt-[-13px] flex h-[27px] w-[27px] -translate-y-0 items-center justify-center rounded-full text-white transition-colors hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-[1399px]:left-[-20px] min-[992px]:left-[-6%] max-[991px]:left-[-10px]"
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
          class="absolute top-1/2 right-[-10px] z-[9] mt-[-13px] flex h-[27px] w-[27px] items-center justify-center rounded-full text-white transition-colors hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-[1399px]:right-[-20px] min-[992px]:right-[-6%] max-[991px]:right-[-10px]"
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
    class="block py-[37px] pb-[76px]"
    aria-labelledby="success-story-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="-mx-[15px] flex flex-wrap">
        <div class="mb-8 w-full px-[15px] md:mb-0 md:w-2/3">
          <div>
            <h2
              id="success-story-heading"
              class="mb-[5px] border-b border-solid border-[#d9d9d9] text-lg leading-[38px] font-bold text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
            >
              Success Story
              <NuxtLink
                to="/about"
                class="float-right mt-2.5 text-xs leading-7 text-[#f15b22] transition-colors hover:text-black hover:[&_.icon]:text-black"
              >
                <span
                  class="icon icon-eye mr-[5px] inline-block align-middle text-sm leading-7 text-[#f15b22]"
                  aria-hidden="true"
                />
                View All
              </NuxtLink>
            </h2>

            <article
              v-for="story in stories"
              :key="story.name"
              class="relative mt-[27px] block p-0 pt-1.5 max-[479px]:p-0 md:pl-[100px]"
            >
              <div
                class="mx-auto mb-2.5 w-[74px] overflow-hidden rounded-full border-2 border-solid border-[#c6c6c6] max-[479px]:static md:absolute md:top-0 md:left-[7px] md:mx-0 md:mb-0"
              >
                <img
                  :src="story.image"
                  :alt="story.name"
                  class="block h-auto w-full"
                  loading="lazy"
                >
              </div>

              <div>
                <p class="m-0 text-sm leading-6 text-[#888888]">
                  {{ story.text }}
                </p>
                <div class="block text-right text-sm leading-6 text-black italic">
                  - {{ story.name }}
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="w-full px-[15px] md:w-1/3">
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
                  class="mt-1 text-xs text-[#e7480b]"
                >
                  {{ shareErrors.name }}
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
                  class="mt-1 text-xs text-[#e7480b]"
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
                  class="min-h-24 w-full resize-none rounded-[5px] border border-solid border-[#cacbcb] bg-white px-2.5 py-2.5 text-sm leading-[18px] text-[#333] italic outline-none"
                  rows="4"
                />
                <p
                  v-if="shareErrors.story"
                  class="mt-1 text-xs text-[#e7480b]"
                >
                  {{ shareErrors.story }}
                </p>
              </div>

              <div>
                <button
                  type="submit"
                  class="w-full rounded-[3px] border border-solid border-[#f15b22] bg-[#f15b22] py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                >
                  Share Your Story
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </UContainer>
  </section>
</template>
