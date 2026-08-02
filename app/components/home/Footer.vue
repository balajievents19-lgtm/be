<script setup lang="ts">
import { reactive } from 'vue'
import {
  footerCompanyLinks,
  footerSocialLinks,
  footerUpdates,
  siteContact
} from '~/data/home'

const newsletter = reactive({
  firstName: '',
  lastName: '',
  email: ''
})

const copyrightYear = new Date().getFullYear()

const onNewsletterSubmit = (event: Event) => {
  event.preventDefault()
  newsletter.firstName = ''
  newsletter.lastName = ''
  newsletter.email = ''
}
</script>

<template>
  <footer
    id="footer"
    class="bg-[#0e1123]"
  >
    <div class="py-9 pb-[34px] max-[767px]:pb-2.5">
      <UContainer class="mx-auto max-w-[1170px]">
        <div class="-mx-[15px] flex flex-wrap">
          <div class="mb-6 w-full px-[15px] sm:w-1/2 lg:mb-0 lg:w-1/4">
            <h5
              class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
            >
              Latest Updates
            </h5>

            <div
              v-for="(item, index) in footerUpdates"
              :key="`update-${index}`"
              class="relative my-[3px] mb-[7px] inline-block min-h-14 w-full pl-[70px]"
            >
              <div class="absolute top-0 left-0 w-[60px] border border-solid border-[#b69c9c]">
                <img
                  :src="item.image"
                  alt=""
                  class="block h-auto w-full"
                  loading="lazy"
                  decoding="async"
                >
              </div>
              <div>
                <p class="m-0 text-xs leading-[18px] text-[#83879b]">
                  {{ item.text }}
                </p>
                <NuxtLink
                  :to="item.to"
                  class="text-[13px] leading-[18px] text-[#e7480b] no-underline transition-colors hover:text-[#fffffe] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                >
                  Read More
                </NuxtLink>
              </div>
            </div>
          </div>

          <div class="mb-6 w-full px-[15px] text-left sm:w-1/2 lg:mb-0 lg:w-1/4">
            <div class="inline-block min-h-0 w-full max-w-[157px] text-left md:min-h-[260px] max-[639px]:min-h-0">
              <h5
                class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
              >
                Company
              </h5>
              <ul class="m-0 list-none p-0">
                <li
                  v-for="link in footerCompanyLinks"
                  :key="link.label"
                  class="group"
                >
                  <NuxtLink
                    :to="link.to"
                    class="relative block py-0 pr-0 pl-[15px] text-[13px] leading-7 text-[#85889b] transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22] before:absolute before:top-2.5 before:left-0 before:h-[9px] before:w-2 before:bg-[url('/images/footer-arrow.png')] before:bg-left before:bg-no-repeat before:content-[''] group-hover:before:bg-[url('/images/footer-arrowHover.png')]"
                  >
                    {{ link.label }}
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </div>

          <div class="mb-6 w-full px-[15px] sm:w-1/2 lg:mb-0 lg:w-1/4">
            <div>
              <h5
                class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
              >
                Contact us
              </h5>

              <div class="relative pb-[7px] pl-5">
                <span
                  class="icon icon-location-1 absolute top-1 left-0 text-[#85889b]"
                  aria-hidden="true"
                />
                <p class="m-0 w-full max-w-[195px] text-[13px] leading-6 text-[#85889b]">
                  {{ siteContact.address }}
                </p>
              </div>

              <div class="relative pb-[7px] pl-5">
                <span
                  class="icon icon-phone absolute top-1 left-0 text-[#85889b]"
                  aria-hidden="true"
                />
                <p class="m-0 w-full max-w-[195px] text-[13px] leading-6 text-[#85889b]">
                  <a
                    :href="siteContact.phoneHref"
                    class="text-[13px] leading-6 text-[#85889b] transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                  >{{ siteContact.phoneDisplay }}</a>
                </p>
              </div>

              <div class="group relative pb-[7px] pl-5">
                <span
                  class="icon icon-message absolute top-1 left-0 text-[#85889b] transition-colors group-hover:text-white"
                  aria-hidden="true"
                />
                <a
                  :href="`mailto:${siteContact.email}`"
                  class="text-[13px] leading-6 text-[#85889b] transition-colors group-hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                >
                  {{ siteContact.email }}
                </a>
              </div>
            </div>
          </div>

          <div class="mb-6 w-full px-[15px] sm:w-1/2 lg:mb-0 lg:w-1/4">
            <div class="inline-block w-full">
              <h5
                class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
              >
                Connect with us
              </h5>
              <p class="mb-3 max-w-[255px] text-[13px] leading-6 text-[#85889b]">
                We'll keep you informed and updated Sign up for our email newsletters
              </p>

              <form
                novalidate
                @submit="onNewsletterSubmit"
              >
                <div class="relative clear-both -mx-[5px]">
                  <div class="mb-2.5 w-1/2 float-left px-[5px]">
                    <label
                      class="sr-only"
                      for="newsletter-first-name"
                    >First Name</label>
                    <input
                      id="newsletter-first-name"
                      v-model="newsletter.firstName"
                      type="text"
                      placeholder="First Name"
                      class="h-[29px] w-full border-none bg-white px-2.5 py-[5px] text-xs leading-[19px] text-[#3d3c3c] outline-none"
                      autocomplete="given-name"
                    >
                  </div>
                  <div class="mb-2.5 w-1/2 float-left px-[5px]">
                    <label
                      class="sr-only"
                      for="newsletter-last-name"
                    >Last Name</label>
                    <input
                      id="newsletter-last-name"
                      v-model="newsletter.lastName"
                      type="text"
                      placeholder="Last Name"
                      class="h-[29px] w-full border-none bg-white px-2.5 py-[5px] text-xs leading-[19px] text-[#3d3c3c] outline-none"
                      autocomplete="family-name"
                    >
                  </div>
                </div>

                <div class="relative clear-both -mx-[5px]">
                  <div class="mb-2.5 w-full float-left px-[5px]">
                    <label
                      class="sr-only"
                      for="newsletter-email"
                    >Email Address</label>
                    <input
                      id="newsletter-email"
                      v-model="newsletter.email"
                      type="email"
                      placeholder="Email Address"
                      class="h-[29px] w-full border-none bg-white py-[5px] pr-[82px] pl-2.5 text-xs leading-[19px] text-[#3d3c3c] outline-none"
                      autocomplete="email"
                    >
                  </div>
                  <div class="absolute top-0 right-[5px] w-[72px]">
                    <button
                      type="submit"
                      class="h-[29px] w-full border-none bg-[#f15b22] text-center text-xs text-white transition-colors duration-1000 hover:bg-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    >
                      Submit
                    </button>
                  </div>
                </div>
              </form>

              <div class="inline-block w-full pt-[13px]">
                <ul class="m-0 list-none p-0">
                  <li
                    v-for="social in footerSocialLinks"
                    :key="social.label"
                    class="mr-1.5 inline-block align-top"
                  >
                    <a
                      :href="social.href"
                      :aria-label="social.label"
                      class="block h-[26px] w-[26px] rounded-full bg-[#26293b] text-center transition-colors hover:bg-[#f15b22] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                      @click.prevent
                    >
                      <span
                        :class="['icon', social.icon, 'mt-0.5 inline-block w-full text-center leading-[26px] text-white']"
                        aria-hidden="true"
                      />
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </UContainer>
    </div>

    <div class="bg-[#070811] py-4 pb-[13px]">
      <UContainer class="mx-auto max-w-[1170px]">
        <p class="m-0 text-center text-[13px] leading-[30px] text-[#85889b]">
          Copyright &copy; <span>{{ copyrightYear }}</span> - BalajiEvents | All Rights Reserved
        </p>
      </UContainer>
    </div>
  </footer>
</template>
