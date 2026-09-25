<script setup lang="ts">
import { formatBlogDate } from '~/utils/content'

const seo = usePageSeo({ type: 'blog-index' })
const { data: posts, pending, failed } = await useBlog()
const { data: settings } = useSettings()
await seo

const breadcrumbs = [
  { label: 'Home', to: '/' },
  { label: 'Blog' }
]

const postImage = (post: { featured_image: string | null, thumbnail: string | null }) =>
  post.featured_image || post.thumbnail || ''
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
        title="Blog"
        :breadcrumbs="breadcrumbs"
      />

      <section
        class="news-view bg-white py-10 pb-[60px]"
        aria-label="Blog posts"
      >
        <UContainer class="mx-auto max-w-[1170px]">
          <p
            v-if="failed"
            class="m-0 py-8 text-center text-sm text-[#888888]"
            role="alert"
          >
            Blog posts are temporarily unavailable.
          </p>
          <p
            v-else-if="pending && !(posts?.length)"
            class="m-0 py-8 text-center text-sm text-[#888888]"
          >
            Loading posts…
          </p>
          <p
            v-else-if="!(posts?.length)"
            class="m-0 py-8 text-center text-sm text-[#888888]"
          >
            No blog posts yet.
          </p>

          <div
            v-else
            class="-mx-[15px] flex flex-wrap"
          >
            <article
              v-for="post in posts"
              :key="post.id"
              class="mb-[30px] w-full px-[15px] min-[768px]:w-1/2 min-[992px]:w-1/3"
            >
              <div class="news-box h-full bg-surface-muted">
                <div class="card-media-frame">
                  <img
                    v-if="postImage(post)"
                    :src="postImage(post)"
                    :alt="post.alt_text || post.title"
                    width="1200"
                    height="1200"
                    loading="lazy"
                    decoding="async"
                  >
                  <div
                    v-else
                    class="card-media-fallback"
                    aria-hidden="true"
                  >
                    <i class="icon icon-camera" />
                  </div>
                </div>
                <div class="p-8">
                  <div class="relative mb-5 pb-[15px]">
                    <h2 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-xl font-bold leading-8 text-[#333333]">
                      <NuxtLink
                        :to="`/blog/${post.slug}`"
                        class="text-[#333333] no-underline transition-colors hover:text-brand-500"
                      >
                        {{ post.title }}
                      </NuxtLink>
                    </h2>
                    <span
                      v-if="formatBlogDate(post.published_at) || post.author"
                      class="block text-sm leading-6 text-[#666]"
                    >
                      <template v-if="post.author && formatBlogDate(post.published_at)">
                        {{ post.author }} on {{ formatBlogDate(post.published_at) }}
                      </template>
                      <template v-else>
                        {{ post.author || formatBlogDate(post.published_at) }}
                      </template>
                    </span>
                    <span
                      class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                      aria-hidden="true"
                    />
                  </div>
                  <p
                    v-if="post.excerpt"
                    class="m-0 pb-6 text-sm leading-6 text-[#666]"
                  >
                    {{ post.excerpt }}
                  </p>
                  <NuxtLink
                    :to="`/blog/${post.slug}`"
                    class="inline-block rounded-[3px] border border-solid border-brand-500 bg-brand-500 px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                  >
                    Read More
                  </NuxtLink>
                </div>
              </div>
            </article>
          </div>
        </UContainer>
      </section>
    </main>

    <HomeFooter
      :settings="settings"
      :updates="posts ?? []"
    />
  </div>
</template>
