<script setup lang="ts">
import type { ContactBox } from '~/data/contact'
import { contactBoxes, contactInfoHeading } from '~/data/contact'

withDefaults(defineProps<{
  headingId?: string
  boxes?: ContactBox[]
  title?: string
  info?: string
}>(), {
  headingId: 'contact-info-heading',
  boxes: undefined,
  title: undefined,
  info: undefined
})
</script>

<template>
  <section
    class="content"
    :aria-labelledby="headingId"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="home-event relative py-[46px] pb-[95px]">
        <div class="mb-0 w-full text-center">
          <div class="block w-full">
            <em
              class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-brand-500"
              aria-hidden="true"
            />
          </div>

          <div class="relative mx-auto my-7 mb-[23px] inline-block w-full max-w-[761px]">
            <div
              class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
              aria-hidden="true"
            />
            <h2
              :id="headingId"
              class="relative z-[2] m-0 inline-block bg-white px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
            >
              {{ title ?? contactInfoHeading.title }}
            </h2>
          </div>

          <p class="info-text mx-auto m-0 inline-block w-full max-w-[761px] text-center text-sm leading-7 text-[#6a6767]">
            {{ info ?? contactInfoHeading.info }}
          </p>

          <div
            class="mx-auto mt-[30px] h-[3px] w-[110px] bg-brand-500"
            aria-hidden="true"
          />
        </div>

        <div class="-mx-[15px] flex flex-wrap">
          <div
            v-for="(box, index) in (boxes ?? contactBoxes)"
            :key="`contact-box-${index}`"
            class="mt-[52px] w-full px-[15px] text-center sm:w-1/3"
          >
            <div class="contact-box inline-block w-full text-center">
              <div class="contactIcon mb-8 inline-block h-[81px] w-[81px] rounded-full bg-brand-500">
                <span
                  :class="[
                    'icon',
                    box.icon,
                    'table-cell h-[81px] w-[81px] align-middle text-center text-[30px] leading-none text-white'
                  ]"
                  aria-hidden="true"
                />
              </div>

              <template v-if="box.type === 'phones' && box.phones">
                <a
                  v-for="phone in box.phones"
                  :key="phone.href"
                  :href="phone.href"
                  class="block text-[#6a6767] transition-colors hover:text-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                >
                  {{ phone.label }}
                </a>
              </template>

              <address
                v-else-if="box.type === 'address'"
                class="m-0 block px-[30px] text-[#6a6767] not-italic"
              >
                {{ box.address }}
              </address>

              <template v-else-if="box.type === 'email' && box.lines">
                <span
                  v-for="(line, lineIndex) in box.lines"
                  :key="`line-${lineIndex}`"
                  class="block text-[#6a6767]"
                >
                  {{ line.label }}
                  <a
                    v-if="line.href"
                    :href="line.href"
                    class="inline-block text-[#6a6767] transition-colors hover:text-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                  >
                    {{ line.text }}
                  </a>
                  <template v-else>
                    {{ line.text }}
                  </template>
                </span>
              </template>
            </div>
          </div>
        </div>
      </div>
    </UContainer>
  </section>
</template>
