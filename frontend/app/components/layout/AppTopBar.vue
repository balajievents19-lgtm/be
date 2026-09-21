<script setup lang="ts">
import type { Ref } from 'vue'
import type { HomePayload } from '~/types/home'

interface TopBarLink {
  label: string
  icon?: string
  to?: string
  action?: 'login' | 'register' | 'logout' | 'account'
}

const home = inject<Ref<HomePayload | null> | null>('home', null)
const { data: settings } = useSettings()
const { customer, openLogin, openRegister, logout } = useCustomerAuth()

const email = computed(
  () => settings.value?.contact?.email || home?.value?.settings?.contact?.email || ''
)

const topBarText = computed(
  () => settings.value?.header?.top_bar_text || home?.value?.settings?.header?.top_bar_text || ''
)

const links = computed<TopBarLink[]>(() => {
  const base: TopBarLink[] = [
    { label: 'Become a Vendor', icon: 'icon-multi-user', to: '/vendor' },
    { label: 'Invite Friends', icon: 'icon-invite-friend', to: '/invite' }
  ]

  if (customer.value) {
    base.push(
      { label: 'Account', action: 'account', to: '/account' },
      { label: 'Logout', action: 'logout' }
    )
  } else {
    base.push(
      { label: 'Registration', action: 'register' },
      { label: 'Login', action: 'login' }
    )
  }

  return base
})

const onClick = async (item: TopBarLink, event: Event) => {
  if (item.action === 'login') {
    event.preventDefault()
    openLogin()
    return
  }
  if (item.action === 'register') {
    event.preventDefault()
    openRegister()
    return
  }
  if (item.action === 'logout') {
    event.preventDefault()
    await logout()
    await navigateTo('/')
  }
}
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
            v-if="item.to && (item.action === 'account' || !item.action)"
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
          <button
            v-else
            type="button"
            class="flex items-center px-[11px] text-[13px] leading-[14px] text-[#d6d8e4] transition-colors hover:text-white sm:px-[15px]"
            :class="{
              'pl-0': index === 0,
              'pr-0': index === links.length - 1
            }"
            @click="onClick(item, $event)"
          >
            <span
              v-if="item.icon"
              :class="['icon', item.icon, 'mr-[5px] text-sm']"
              aria-hidden="true"
            />
            {{ item.label }}
          </button>
        </li>
      </ul>
    </UContainer>
  </div>
</template>
