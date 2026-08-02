<script setup lang="ts">
const services = [
  {
    title: 'Wedding Planning',
    icon: 'i-lucide-heart',
    to: '/services/wedding-planning'
  },
  {
    title: 'Tent House',
    icon: 'i-lucide-tent',
    to: '/services/tent-house'
  },
  {
    title: 'Stage Decoration',
    icon: 'i-lucide-image',
    to: '/services/stage-decoration'
  },
  {
    title: 'Catering',
    icon: 'i-lucide-utensils-crossed',
    to: '/services/catering'
  },
  {
    title: 'Photography',
    icon: 'i-lucide-camera',
    to: '/services/photography'
  },
  {
    title: 'DJ & Sound',
    icon: 'i-lucide-music-4',
    to: '/services/dj-sound'
  },
  {
    title: 'Birthday Party',
    icon: 'i-lucide-cake',
    to: '/services/birthday'
  },
  {
    title: 'Corporate Events',
    icon: 'i-lucide-briefcase-business',
    to: '/services/corporate'
  }
]
</script>

<template>
  <div
    class="absolute left-0 top-full mt-5 w-full rounded-2xl border border-gray-200 bg-white shadow-2xl"
  >
    <div class="grid grid-cols-4 gap-2 p-8">

      <NuxtLink
        v-for="service in services"
        :key="service.title"
        :to="service.to"
        class="group flex items-start gap-4 rounded-xl p-4 transition-all duration-300 hover:bg-orange-50"
      >
        <div
          class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-[#f85a1f] transition-all duration-300 group-hover:bg-[#f85a1f] group-hover:text-white"
        >
          <UIcon
            :name="service.icon"
            class="size-6"
          />
        </div>

        <div>
          <h4
            class="font-semibold text-slate-800 transition group-hover:text-[#f85a1f]"
          >
            {{ service.title }}
          </h4>

          <p class="mt-1 text-sm text-slate-500">
            Professional event solution
          </p>
        </div>

      </NuxtLink>

    </div>
  </div>
</template>