<script setup lang="ts">
const rightLinks = [
  {
    label: 'Become a Vendor',
    to: '/vendor'
  },
  {
    label: 'Invite Friends',
    to: '/invite'
  },
  {
    label: 'Registration',
    to: '/register'
  },
  {
    label: 'Login',
    to: '/login'
  }
]
</script>

<template>
  <div class="bg-[#161b2d] text-white">
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="flex h-10 items-center justify-between">

        <!-- Left -->

        <a
          href="mailto:balajievents19@gmail.com"
          class="flex items-center gap-2 text-[13px] transition hover:text-[#f15b25]"
        >
          <UIcon
            name="i-lucide-mail"
            class="size-4"
          />

          <span>balajievents19@gmail.com</span>
        </a>

        <!-- Right -->

        <div class="hidden items-center lg:flex">

          <template
            v-for="(item,index) in rightLinks"
            :key="item.label"
          >

            <NuxtLink
              :to="item.to"
              class="px-4 text-[13px] transition hover:text-[#f15b25]"
            >
              <UIcon
                v-if="item.label==='Invite Friends'"
                name="i-lucide-user-plus"
                class="mr-1 size-4"
              />

              {{ item.label }}
            </NuxtLink>

            <span
              v-if="index!==rightLinks.length-1"
              class="text-white/30"
            >
              |
            </span>

          </template>

        </div>

      </div>
    </UContainer>
  </div>
</template>