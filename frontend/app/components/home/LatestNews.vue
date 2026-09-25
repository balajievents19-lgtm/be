<script setup lang="ts">
import { computed } from 'vue'
import type { BlogPostItem } from '~/types/home'
import { formatBlogDate } from '~/utils/content'

const props = withDefaults(defineProps<{
  posts?: BlogPostItem[]
  pending?: boolean
  failed?: boolean
}>(), {
  posts: () => [],
  pending: false,
  failed: false
})

const featured = computed(() => props.posts[0] ?? null)
const textCards = computed(() => props.posts.slice(1, 3))
const sideCard = computed(() => props.posts[3] ?? null)

const postImage = (post: BlogPostItem) => post.featured_image || post.thumbnail || ''

const postMeta = (post: BlogPostItem) => {
  const date = formatBlogDate(post.published_at)
  if (post.author && date) {
    return `${post.author} on ${date}`
  }
  return post.author || date
}
</script>

<template>
  <section
    class="news-view bg-surface-muted py-10 pb-[30px]"
    aria-labelledby="latest-news-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="mb-0 w-full pb-[90px] text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-brand-500"
            aria-hidden="true"
          />
        </div>

        <div class="relative mx-auto mt-7 mb-[23px] inline-block w-full max-w-[761px]">
          <div
            class="absolute top-[17px] left-0 h-px w-full bg-[#e5e5e5]"
            aria-hidden="true"
          />
          <h2
            id="latest-news-heading"
            class="relative z-[2] m-0 inline-block bg-surface-muted px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Latest News
          </h2>
        </div>
      </div>

      <p
        v-if="failed"
        class="m-0 py-8 text-center text-sm text-[#888888]"
        role="alert"
      >
        Latest news is temporarily unavailable.
      </p>
      <p
        v-else-if="pending && !posts.length"
        class="m-0 py-8 text-center text-sm text-[#888888]"
      >
        Loading news…
      </p>
      <p
        v-else-if="!posts.length"
        class="m-0 py-8 text-center text-sm text-[#888888]"
      >
        No news to show yet.
      </p>

      <div
        v-else
        class="-mx-[15px] flex flex-wrap"
      >
        <div class="w-full px-[15px] min-[992px]:w-2/3">
          <article
            v-if="featured"
            class="news-box mb-[30px] bg-white"
          >
            <div class="-mx-[15px] flex flex-wrap">
              <div
                v-if="postImage(featured)"
                class="w-full px-[15px] min-[768px]:w-1/2"
              >
                <div class="card-media-frame">
                  <img
                    :src="postImage(featured)"
                    :alt="featured.alt_text || featured.title"
                    width="1200"
                    height="1200"
                    loading="lazy"
                    decoding="async"
                  >
                </div>
              </div>
              <div
                class="w-full px-[15px]"
                :class="postImage(featured) ? 'min-[768px]:w-1/2' : ''"
              >
                <div class="px-5 pt-[35px] pb-11 max-[1199px]:py-[15px] max-[767px]:p-[30px]">
                  <div class="relative mb-[34px] pb-[15px] max-[1199px]:mb-2.5">
                    <h3 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333]">
                      {{ featured.title }}
                    </h3>
                    <span
                      v-if="postMeta(featured)"
                      class="block text-sm leading-6 text-[#666]"
                    >
                      {{ postMeta(featured) }}
                    </span>
                    <span
                      class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                      aria-hidden="true"
                    />
                  </div>
                  <p
                    v-if="featured.excerpt"
                    class="m-0 w-full max-w-[280px] pb-6 text-sm leading-6 text-[#666]"
                  >
                    {{ featured.excerpt }}
                  </p>
                  <NuxtLink
                    :to="`/blog/${featured.slug}`"
                    class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                  >
                    Read More
                  </NuxtLink>
                </div>
              </div>
            </div>
          </article>

          <div
            v-if="textCards.length"
            class="-mx-[15px] flex flex-wrap"
          >
            <div
              v-for="card in textCards"
              :key="card.id"
              class="w-full px-[15px] min-[768px]:w-1/2"
            >
              <article class="news-box style2 mb-[30px] bg-white">
                <div class="p-10 max-[767px]:p-[30px]">
                  <div class="relative mb-[34px] pb-[15px] max-[1199px]:mb-2.5">
                    <h3 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333]">
                      {{ card.title }}
                    </h3>
                    <span
                      v-if="postMeta(card)"
                      class="block text-sm leading-6 text-[#666]"
                    >
                      {{ postMeta(card) }}
                    </span>
                    <span
                      class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                      aria-hidden="true"
                    />
                  </div>
                  <p
                    v-if="card.excerpt"
                    class="m-0 w-full max-w-[280px] pb-6 text-sm leading-6 text-[#666]"
                  >
                    {{ card.excerpt }}
                  </p>
                  <NuxtLink
                    :to="`/blog/${card.slug}`"
                    class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                  >
                    Read More
                  </NuxtLink>
                </div>
              </article>
            </div>
          </div>
        </div>

        <div
          v-if="sideCard"
          class="w-full px-[15px] min-[992px]:w-1/3"
        >
          <article class="news-box style3 mb-[30px] overflow-hidden bg-white">
            <div class="max-[991px]:overflow-hidden max-[767px]:block">
              <div
                v-if="postImage(sideCard)"
                class="card-media-frame"
              >
                <img
                  :src="postImage(sideCard)"
                  :alt="sideCard.alt_text || sideCard.title"
                  width="1200"
                  height="1200"
                  loading="lazy"
                  decoding="async"
                >
              </div>
              <div
                class="px-[50px] pt-[50px] pb-[60px] max-[991px]:float-left max-[991px]:w-1/2 max-[991px]:pt-[30px] max-[767px]:float-none max-[767px]:w-full max-[767px]:p-[30px]"
              >
                <div class="relative mb-[34px] pb-[15px] max-[1199px]:mb-2.5">
                  <h3 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333]">
                    {{ sideCard.title }}
                  </h3>
                  <span
                    v-if="postMeta(sideCard)"
                    class="block text-sm leading-6 text-[#666]"
                  >
                    {{ postMeta(sideCard) }}
                  </span>
                  <span
                    class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                    aria-hidden="true"
                  />
                </div>
                <p
                  v-if="sideCard.excerpt"
                  class="m-0 w-full max-w-[280px] pb-6 text-sm leading-6 text-[#666]"
                >
                  {{ sideCard.excerpt }}
                </p>
                <NuxtLink
                  :to="`/blog/${sideCard.slug}`"
                  class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                >
                  Read More
                </NuxtLink>
              </div>
            </div>
          </article>
        </div>
      </div>
    </UContainer>
  </section>
</template>
