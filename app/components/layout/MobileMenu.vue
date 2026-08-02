<script setup lang="ts">
defineProps<{
  open: boolean
}>()

defineEmits<{
  close: []
}>()

const servicesOpen = ref(false)

const menu = [
  {
    label: 'Home',
    to: '/'
  },
  {
    label: 'About',
    to: '/about'
  },
  {
    label: 'FAQ',
    to: '/faq'
  },
  {
    label: 'Contact',
    to: '/contact'
  }
]

const services = [
  'Wedding Planning',
  'Tent House',
  'Stage Decoration',
  'Catering',
  'Photography',
  'DJ & Sound',
  'Birthday Party',
  'Corporate Event'
]
</script>

<template>
  <Transition
    enter-active-class="transition duration-300"
    leave-active-class="transition duration-300"
    enter-from-class="translate-x-full"
    leave-to-class="translate-x-full"
  >
    <div
      v-if="open"
      class="fixed inset-0 z-[999]"
    >
      <!-- Overlay -->

      <div
        class="absolute inset-0 bg-black/60"
        @click="$emit('close')"
      />

      <!-- Drawer -->

      <div
        class="absolute right-0 top-0 h-full w-[320px] overflow-y-auto bg-white shadow-2xl"
      >
        <!-- Header -->

        <div
          class="flex items-center justify-between border-b p-5"
        >
          <img
            src="/images/logo.png"
            class="h-14 w-auto"
          >

          <UButton
            square
            color="neutral"
            variant="ghost"
            icon="i-lucide-x"
            @click="$emit('close')"
          />
        </div>

        <!-- Menu -->

        <div class="p-6">

          <NuxtLink
            v-for="item in menu"
            :key="item.label"
            :to="item.to"
            class="block border-b py-4 font-medium text-slate-700 hover:text-[#f85a1f]"
            @click="$emit('close')"
          >
            {{ item.label }}
          </NuxtLink>

          <!-- Services -->

          <button
            class="flex w-full items-center justify-between border-b py-4 font-medium"
            @click="servicesOpen=!servicesOpen"
          >
            Services

            <UIcon
              :name="servicesOpen
                ? 'i-lucide-chevron-up'
                : 'i-lucide-chevron-down'"
            />
          </button>

          <Transition
            enter-active-class="transition duration-300"
            leave-active-class="transition duration-300"
          >
            <div
              v-if="servicesOpen"
              class="border-b bg-gray-50"
            >
              <NuxtLink
                v-for="service in services"
                :key="service"
                to="/"
                class="block px-5 py-3 text-sm text-slate-600 hover:bg-orange-50 hover:text-[#f85a1f]"
              >
                {{ service }}
              </NuxtLink>
            </div>
          </Transition>

          <!-- CTA -->

          <div class="mt-8">

            <UButton
              block
              size="xl"
              color="primary"
              to="/contact"
            >
              Book Now
            </UButton>

          </div>

        </div>

      </div>

    </div>
  </Transition>
</template>