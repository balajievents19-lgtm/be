<script setup lang="ts">
import type { Ref } from 'vue'
import type { HomePayload } from '~/types/home'

interface TopBarLink {
  label: string
  icon?: string
  to: string
}

const home = inject<Ref<HomePayload | null> | null>('home', null)
const { data: settings } = useSettings()

const email = computed(
  () => settings.value?.contact?.email || home?.value?.settings?.contact?.email || ''
)

const topBarText = computed(
  () => settings.value?.header?.top_bar_text || home?.value?.settings?.header?.top_bar_text || ''
)

const links: TopBarLink[] = [
  { label: 'Become a Vendor', icon: 'icon-multi-user', to: '/vendor' },
  { label: 'Invite Friends', icon: 'icon-invite-friend', to: '/invite' },
  { label: 'Registration', to: '/register' },
  { label: 'Login', to: '/login' }
]
</script>

<template>
  <div class="quck-link bg-navy-500 py-[3px]">
    <UContainer class="mx-auto flex max-w-[1170px] flex-col items-center gap-1 md:h-5 md:flex-row md:justify-between md:gap-0">
      <a
        v-if="email"
        :href="`mailto:${email}`"
        class="inline-flex items-center text-[13px] leading-[14px] text-[#d6d8e4] transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
      >
        <span
          class="icon icon-message-1 mr-[5px] text-sm"
          aria-hidden="true"
        />
        {{ email }}
      </a>
      <span
        v-else-if="topBarText"
        class="inline-flex items-center text-[13px] leading-[14px] text-[#d6d8e4]"
      >
        {{ topBarText }}
      </span>
      <span
        v-else
        class="inline-block h-[14px]"
        aria-hidden="true"
      />

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
