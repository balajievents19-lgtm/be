<script setup lang="ts">
import { reactive, ref } from 'vue'

const form = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const errors = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const submitted = ref(false)

const validate = () => {
  errors.name = form.name.trim() ? '' : 'Name cannot be blank.'
  errors.email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())
    ? ''
    : 'Incorrect e-mail address'
  errors.subject = form.subject.trim() ? '' : 'Subject cannot be blank.'
  errors.message = form.message.trim() ? '' : 'Message cannot be blank.'
  return !errors.name && !errors.email && !errors.subject && !errors.message
}

const onSubmit = (event: Event) => {
  event.preventDefault()
  submitted.value = false
  if (!validate()) {
    return
  }
  submitted.value = true
  form.name = ''
  form.email = ''
  form.subject = ''
  form.message = ''
}
</script>

<template>
  <section
    class="contackForm py-20"
    aria-labelledby="contact-form-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <h2
        id="contact-form-heading"
        class="m-0 mb-6 text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
      >
        Contact Form
      </h2>

      <form
        class="-mx-[15px] flex flex-wrap"
        novalidate
        @submit="onSubmit"
      >
        <div class="w-full px-[15px] md:w-1/2">
          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-name"
            >
              Your Name <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-name"
              v-model="form.name"
              type="text"
              name="name"
              autocomplete="name"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none"
            >
            <p
              v-if="errors.name"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.name }}
            </p>
          </div>

          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-email"
            >
              Your Email <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-email"
              v-model="form.email"
              type="text"
              name="email"
              autocomplete="email"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none"
            >
            <p
              v-if="errors.email"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.email }}
            </p>
          </div>

          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-subject"
            >
              Subject <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <input
              id="contact-subject"
              v-model="form.subject"
              type="text"
              name="subject"
              class="box-border h-[38px] w-full border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none"
            >
            <p
              v-if="errors.subject"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.subject }}
            </p>
          </div>
        </div>

        <div class="w-full px-[15px] md:w-1/2">
          <div class="mb-[22px]">
            <label
              class="mb-1.5 block font-normal text-[#666]"
              for="contact-message"
            >
              Your Message <sup class="top-[-0.1em] text-base text-[#ff0000]">*</sup>
            </label>
            <textarea
              id="contact-message"
              v-model="form.message"
              name="message"
              class="box-border h-[218px] w-full resize-none border border-solid border-[#e8e8e8] px-2.5 py-2.5 text-sm text-[#333] outline-none"
            />
            <p
              v-if="errors.message"
              class="mt-1 text-xs text-[#ff0000]"
            >
              {{ errors.message }}
            </p>
          </div>

          <button
            type="submit"
            class="float-right w-[170px] cursor-pointer rounded-[3px] border border-solid border-brand-500 bg-brand-500 py-[14px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:border-brand-600 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
          >
            Submit
          </button>

          <p
            v-if="submitted"
            class="clear-both mt-4 text-sm text-[#6a6767]"
            role="status"
          >
            Thank you. Your message has been recorded.
          </p>
        </div>
      </form>
    </UContainer>
  </section>
</template>
