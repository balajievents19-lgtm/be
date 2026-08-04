<script setup lang="ts">
import { formatBlogDate } from '~/utils/content'

const route = useRoute()
const slug = computed(() => String(route.params.slug || ''))

const { data: post, pending, failed } = await useBlogPost(slug.value)
const { data: settings } = await useSettings()

const breadcrumbs = computed(() => [
  { label: 'Home', to: '/' },
  { label: 'Blog', to: '/blog' },
  { label: post.value?.title || 'Post' }
])

const postImage = computed(() =>
  post.value?.featured_image || post.value?.thumbnail || ''
)

useSeoMeta({
  title: () => post.value?.seo?.title || (post.value?.title ? `${post.value.title} | Balaji Events` : 'Blog | Balaji Events'),
  description: () => post.value?.seo?.description
    || post.value?.excerpt
    || settings.value?.seo?.meta_description
    || undefined,
  ogTitle: () => post.value?.seo?.title || post.value?.title || 'Blog',
  ogDescription: () => post.value?.seo?.description || post.value?.excerpt || undefined,
  ogImage: () => post.value?.seo?.opengraph_image || postImage.value || settings.value?.seo?.opengraph_image || undefined,
  twitterCard: 'summary_large_image'
})

useHead(() => {
  const href = post.value?.seo?.canonical_url
    || (settings.value?.seo?.canonical_url && slug.value
      ? `${settings.value.seo.canonical_url.replace(/\/$/, '')}/blog/${slug.value}`
      : undefined)
  return href ? { link: [{ rel: 'canonical' as const, href }] } : {}
})

if (!post.value && !pending.value) {
  throw createError({ statusCode: 404, statusMessage: 'Blog post not found' })
}
</script>

<template>
  <div class="page relative bg-white text-[#333333] max-md:pt-0 md:pt-[121px]">
    <a
      href="#main-content"
      class="absolute left-[-10000px] top-auto z-[10001] h-px w-px overflow-hidden focus:left-2 focus:top-2 focus:h-auto focus:w-auto focus:overflow-visible focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:text-navy-500 focus:outline focus:outline-2 focus:outline-brand-500"
    >
      Skip to main content
    </a>

    <LayoutAppHeader />

    <main id="main-content">
      <SharedPageHeader
        :title="post?.title || 'Blog'"
        :breadcrumbs="breadcrumbs"
      />

      <section
        class="news-view bg-white py-10 pb-[60px]"
        aria-label="Blog post"
      >
        <UContainer class="mx-auto max-w-[1170px]">
          <p
            v-if="failed && !post"
            class="m-0 py-8 text-center text-sm text-[#888888]"
            role="alert"
          >
            This post could not be loaded.
          </p>
          <p
            v-else-if="pending && !post"
            class="m-0 py-8 text-center text-sm text-[#888888]"
          >
            Loading post…
          </p>

          <article
            v-else-if="post"
            class="news-box mx-auto max-w-[770px] bg-white"
          >
            <img
              v-if="postImage"
              :src="postImage"
              :alt="post.alt_text || post.title"
              class="mb-6 block h-auto w-full"
              width="770"
              height="420"
              loading="eager"
              decoding="async"
            >

            <div class="relative mb-6 pb-[15px]">
              <h1 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333] md:text-3xl">
                {{ post.title }}
              </h1>
              <span
                v-if="formatBlogDate(post.published_at) || post.author"
                class="mt-2 block text-sm leading-6 text-[#666]"
              >
                <template v-if="post.author && formatBlogDate(post.published_at)">
                  {{ post.author }} on {{ formatBlogDate(post.published_at) }}
                </template>
                <template v-else>
                  {{ post.author || formatBlogDate(post.published_at) }}
                </template>
                <template v-if="post.reading_time">
                  · {{ post.reading_time }} min read
                </template>
              </span>
              <span
                class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                aria-hidden="true"
              />
            </div>

            <!-- eslint-disable-next-line vue/no-v-html -- trusted CMS HTML from admin -->
            <div
              v-if="post.content"
              class="prose-blog text-sm leading-7 text-[#666] [&_a]:text-brand-500 [&_h2]:mb-3 [&_h2]:mt-6 [&_h2]:font-['Domine',Georgia,'Times_New_Roman',serif] [&_h2]:text-xl [&_h2]:text-[#333] [&_h3]:mb-2 [&_h3]:mt-5 [&_h3]:text-lg [&_h3]:text-[#333] [&_img]:my-4 [&_img]:h-auto [&_img]:w-full [&_li]:mb-1 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-4 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-5"
              v-html="post.content"
            />
            <p
              v-else-if="post.excerpt"
              class="m-0 text-sm leading-7 text-[#666]"
            >
              {{ post.excerpt }}
            </p>

            <div class="mt-10">
              <NuxtLink
                to="/blog"
                class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
              >
                Back to Blog
              </NuxtLink>
            </div>
          </article>
        </UContainer>
      </section>
    </main>

    <HomeFooter :settings="settings" />
  </div>
</template>
