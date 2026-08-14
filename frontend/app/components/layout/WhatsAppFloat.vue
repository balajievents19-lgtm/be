<script setup lang="ts">
import { whatsappHref } from '~/utils/content'

const WHATSAPP_ENQUIRY_MESSAGE
  = 'Hello Balaji Events, I would like to enquire about your event services.'

const { data: settings } = useSettings()

const href = computed(() => {
  const number = settings.value?.contact?.whatsapp?.trim()
  if (!number) {
    return null
  }
  return whatsappHref(number, WHATSAPP_ENQUIRY_MESSAGE)
})
</script>

<template>
  <a
    v-if="href"
    :href="href"
    target="_blank"
    rel="noopener noreferrer"
    class="whatsapp-float fixed right-4 bottom-20 z-[90] flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366] text-white shadow-[0_4px_14px_rgba(0,0,0,0.22)] transition-transform hover:scale-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366] md:right-6 md:bottom-6 md:h-14 md:w-14"
    aria-label="Chat with Balaji Events on WhatsApp"
  >
    <UIcon
      name="i-simple-icons-whatsapp"
      class="h-6 w-6 md:h-7 md:w-7"
      aria-hidden="true"
    />
  </a>
</template>
