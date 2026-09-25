<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import type { BlogPostItem, SiteSettings } from '~/types/home'

const props = defineProps<{
  settings?: SiteSettings | null
  updates?: BlogPostItem[]
}>()

const { submitNewsletter } = usePublicForms()

const companyLinks = [
  { label: 'Home', to: '/' },
  { label: 'About', to: '/about' },
  { label: 'Services', to: '/services' },
  { label: 'Gallery', to: '/gallery' },
  { label: 'FAQ', to: '/faq' },
  { label: 'Contact', to: '/contact' },
  { label: 'Blog', to: '/blog' }
] as const

const socialIconMap: Record<string, string> = {
  facebook: 'icon-facebook',
  twitter: 'icon-twitter',
  linkedin: 'icon-linkedin',
  youtube: 'icon-play',
  instagram: 'icon-instagram'
}

const newsletter = reactive({
  firstName: '',
  lastName: '',
  email: '',
  website: ''
})

const newsletterSubmitting = ref(false)
const newsletterSent = ref(false)
const newsletterError = ref('')

const copyrightYear = new Date().getFullYear()

const contact = computed(() => {
  const c = props.settings?.contact
  return {
    email: c?.email || '',
    phoneDisplay: c?.phone || '',
    phoneHref: c?.phone ? `tel:${c.phone.replace(/[^\d+]/g, '')}` : '',
    address: c?.address || ''
  }
})

const socialLinks = computed(() => {
  const social = props.settings?.social
  if (!social) {
    return []
  }

  return (Object.keys(socialIconMap) as Array<keyof typeof socialIconMap>)
    .map((key) => {
      const href = social[key as keyof typeof social]
      if (!href) {
        return null
      }
      return {
        key,
        href,
        icon: socialIconMap[key],
        label: key.charAt(0).toUpperCase() + key.slice(1)
      }
    })
    .filter((link): link is NonNullable<typeof link> => link !== null)
})

const latestUpdates = computed(() => (props.updates ?? []).slice(0, 3))

const updateImage = (post: BlogPostItem) => post.thumbnail || post.featured_image || ''

const updateText = (post: BlogPostItem) => post.excerpt || post.title

const copyrightText = computed(() =>
  props.settings?.footer?.copyright_text
  || `Copyright © ${copyrightYear} - Balaji Royal Events | All Rights Reserved`
)

const onNewsletterSubmit = async (event: Event) => {
  event.preventDefault()
  newsletterSent.value = false
  newsletterError.value = ''

  if (newsletter.website.trim()) {
    return
  }

  const email = newsletter.email.trim()
  if (!email) {
    newsletterError.value = 'Email is required.'
    return
  }

  newsletterSubmitting.value = true
  try {
    await submitNewsletter({
      first_name: newsletter.firstName.trim() || null,
      last_name: newsletter.lastName.trim() || null,
      email,
      website: ''
    })
    newsletterSent.value = true
    newsletter.firstName = ''
    newsletter.lastName = ''
    newsletter.email = ''
    newsletter.website = ''
  } catch (error: unknown) {
    const err = error as { data?: { message?: string }, message?: string }
    newsletterError.value = err?.data?.message || err?.message || 'Unable to subscribe. Please try again.'
  } finally {
    newsletterSubmitting.value = false
  }
}
</script>

<template>
  <footer
    id="footer"
    class="bg-navy-500"
  >
    <div class="footer-top pt-9 pb-[34px] max-[767px]:pb-2.5">
      <UContainer class="mx-auto max-w-[1170px]">
        <div class="-mx-[15px] flex flex-wrap">
          <div class="mb-0 w-full px-[15px] max-[991px]:mb-6 sm:w-1/2 lg:w-1/4">
            <h5
              class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
            >
              Latest Updates
            </h5>

            <div
              v-for="item in latestUpdates"
              :key="item.id"
              class="relative my-[3px] mb-[7px] inline-block min-h-14 w-full"
              :class="{ 'pl-[70px]': !!updateImage(item) }"
            >
              <div
                v-if="updateImage(item)"
                class="absolute top-0 left-0 w-[60px] border border-solid border-[#b69c9c]"
              >
                <img
                  :src="updateImage(item)"
                  :alt="item.alt_text || item.title"
                  class="block h-auto w-full"
                  width="60"
                  height="60"
                  loading="lazy"
                  decoding="async"
                >
              </div>
              <div>
                <p class="m-0 text-xs leading-[18px] text-[#83879b]">
                  {{ updateText(item) }}
                </p>
                <NuxtLink
                  :to="`/blog/${item.slug}`"
                  class="text-[13px] leading-[18px] text-brand-600 no-underline transition-colors hover:text-[#fffffe] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                >
                  Read More
                </NuxtLink>
              </div>
            </div>
          </div>

          <div class="mb-0 w-full px-[15px] max-[991px]:mb-6 sm:w-1/2 lg:w-1/4">
            <div class="inline-block min-h-0 w-full max-w-[157px] text-left md:min-h-[260px] max-[639px]:min-h-0">
              <h5
                class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
              >
                Company
              </h5>
              <ul class="m-0 list-none p-0">
                <li
                  v-for="link in companyLinks"
                  :key="link.label"
                  class="group"
                >
                  <NuxtLink
                    :to="link.to"
                    class="relative block py-0 pr-0 pl-[15px] text-[13px] leading-7 text-[#85889b] transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 before:absolute before:top-2.5 before:left-0 before:h-[9px] before:w-2 before:bg-[url('/images/footer-arrow.png')] before:bg-left before:bg-no-repeat before:content-[''] group-hover:before:bg-[url('/images/footer-arrowHover.png')]"
                  >
                    {{ link.label }}
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </div>

          <div class="mb-0 w-full px-[15px] max-[991px]:mb-6 sm:w-1/2 lg:w-1/4">
            <div>
              <h5
                class="mb-[23px] font-['Domine',Georgia,'Times_New_Roman',serif] text-lg font-bold leading-[22px] text-white max-[767px]:my-5"
              >
                Contact us
              </h5>

              <div
                v-if="contact.address"
                class="relative pb-[7px] pl-5"
              >
                <span
                  class="icon icon-location-1 absolute top-1 left-0 text-[#85889b]"
                  aria-hidden="true"
                />
                <p class="m-0 w-full max-w-[195px] text-[13px] leading-6 text-[#85889b]">
                  {{ contact.address }}
                </p>
              </div>

              <div
                v-if="contact.phoneDisplay"
                class="relative pb-[7px] pl-5"
              >
                <span
                  class="icon icon-phone absolute top-1 left-0 text-[#85889b]"
                  aria-hidden="true"
                />
                <p class="m-0 w-full max-w-[195px] text-[13px] leading-6 text-[#85889b]">
                  <a
                    :href="contact.phoneHref"
                    class="text-[13px] leading-6 text-[#85889b] transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                  >{{ contact.phoneDisplay }}</a>
                </p>
              </div>

              <div
                v-if="contact.email"
                class="group relative pb-[7px] pl-5"
              >
                <span
                  class="icon icon-message absolute top-1 left-0 text-[#85889b] transition-colors group-hover:text-white"
                  aria-hidden="true"
                />
                <a
                  :href="`mailto:${contact.email}`"
                  class="text-[13px] leading-6 text-[#85889b] transition-colors group-hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                >
                  {{ contact.email }}
                </a>
              </div>
            </div>
          </div>

          <div class="mb-0 w-full px-[15px] max-[991px]:mb-6 sm:w-1/2 lg:w-1/4">
            <div class="contact-form inline-block w-full">
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
                <input
                  v-model="newsletter.website"
                  type="text"
                  name="website"
                  tabindex="-1"
                  autocomplete="off"
                  class="absolute left-[-10000px] h-px w-px overflow-hidden"
                  aria-hidden="true"
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
                      :disabled="newsletterSubmitting"
                      class="h-[29px] w-full border-none bg-brand-500 text-center text-xs text-white transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:opacity-60"
                    >
                      Submit
                    </button>
                  </div>
                </div>

                <p
                  v-if="newsletterError"
                  class="mt-1 text-xs text-brand-500"
                  role="alert"
                >
                  {{ newsletterError }}
                </p>
                <p
                  v-if="newsletterSent"
                  class="mt-1 text-xs text-[#85889b]"
                  role="status"
                >
                  Subscribed. Thank you!
                </p>
              </form>

              <div
                v-if="socialLinks.length"
                class="inline-block w-full pt-[13px]"
              >
                <ul class="m-0 list-none p-0">
                  <li
                    v-for="social in socialLinks"
                    :key="social.key"
                    class="mr-[6px] inline-block align-top last:mr-0"
                  >
                    <a
                      :href="social.href"
                      :aria-label="social.label"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="block h-[26px] w-[26px] rounded-full bg-navy-400 text-center transition-colors hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
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

    <div class="bg-navy-600 pt-4 pb-[13px]">
      <UContainer class="mx-auto max-w-[1170px]">
        <p class="m-0 text-center text-[13px] leading-[30px] text-[#85889b]">
          {{ copyrightText }}
        </p>
      </UContainer>
    </div>
  </footer>
</template>
