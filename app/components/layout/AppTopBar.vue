<script setup lang="ts">
import { siteContact } from '~/data/home'

interface TopBarLink {
  label: string
  icon?: string
  to: string
}

const links: TopBarLink[] = [
  { label: 'Become a Vendor', icon: 'icon-multi-user', to: '/vendor' },
  { label: 'Invite Friends', icon: 'icon-invite-friend', to: '/invite' },
  { label: 'Registration', to: '/register' },
  { label: 'Login', to: '/login' }
]
</script>

<template>
  <div class="quck-link bg-[#0e1123] py-[3px]">
    <UContainer class="mx-auto flex max-w-[1170px] flex-col items-center gap-1 md:h-5 md:flex-row md:justify-between md:gap-0">
      <a
        :href="`mailto:${siteContact.email}`"
        class="inline-flex items-center text-[13px] leading-[14px] text-[#d6d8e4] transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
      >
        <span
          class="icon icon-message-1 mr-[5px] text-sm"
          aria-hidden="true"
        />
        {{ siteContact.email }}
      </a>

      <ul class="m-0 flex list-none items-center p-0">
        <li
          v-for="(item, index) in links"
          :key="item.label"
          class="relative"
          :class="{ 'border-l border-[#6d7083]': index > 0 }"
        >
          <NuxtLink
            :to="item.to"
            class="flex items-center px-[11px] text-[13px] leading-[14px] text-[#d6d8e4] transition-colors hover:text-white sm:px-[15px]"
            :class="{
              'pl-0': index === 0,
              'pr-0': index === links.length - 1
            }"
          >
            <span
              v-if="item.icon"
              :class="['icon', item.icon, 'mr-[5px] text-sm']"
              aria-hidden="true"
            />
            {{ item.label }}
          </NuxtLink>
        </li>
      </ul>
    </UContainer>
  </div>
</template>
