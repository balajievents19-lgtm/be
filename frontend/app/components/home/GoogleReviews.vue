<script setup lang="ts">
import type { GoogleReviewsPayload } from '~/types/home'

const props = withDefaults(defineProps<{
  reviews?: GoogleReviewsPayload | null
  pending?: boolean
  failed?: boolean
}>(), {
  reviews: null,
  pending: false,
  failed: false
})

const showSection = computed(() => {
  if (props.pending) {
    return true
  }
  if (props.reviews?.configured) {
    return true
  }
  return Boolean(props.reviews?.maps_url)
})

const stars = (rating: number | null | undefined) => {
  const value = Math.round(Number(rating || 0))
  return Math.min(5, Math.max(0, value))
}

const formatDate = (value: string | null | undefined) => {
  if (!value) {
    return ''
  }
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return ''
  }
  return new Intl.DateTimeFormat('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }).format(date)
}
</script>

<template>
  <section
    v-if="showSection"
    class="bg-[#fbf7f2] py-16"
    aria-labelledby="google-reviews-heading"
  >
    <UContainer class="mx-auto max-w-[1170px] px-4">
      <div class="mb-10 text-center">
        <p class="m-0 text-[11px] font-semibold tracking-[0.24em] text-[#b8863b] uppercase">
          Google Reviews
        </p>
        <h2
          id="google-reviews-heading"
          class="mt-3 font-['Domine',Georgia,serif] text-3xl text-[#333]"
        >
          What guests say on Google
        </h2>
        <p
          v-if="reviews?.rating"
          class="mt-3 text-lg text-[#333]"
        >
          <span class="font-semibold text-[#b8863b]">{{ '★'.repeat(stars(reviews.rating)) }}</span>
          <span class="ml-2">{{ reviews.rating.toFixed(1) }}</span>
          <span
            v-if="reviews.review_count"
            class="text-[#666]"
          > ({{ reviews.review_count }} Google reviews)</span>
        </p>
      </div>

      <p
        v-if="failed && !reviews?.reviews?.length"
        class="text-center text-sm text-[#666]"
        role="alert"
      >
        Google reviews are temporarily unavailable.
      </p>
      <p
        v-else-if="pending && !reviews?.reviews?.length && reviews?.configured"
        class="text-center text-sm text-[#666]"
        role="status"
      >
        Loading Google reviews…
      </p>
      <p
        v-else-if="reviews?.configured && reviews.error && !reviews.reviews.length"
        class="text-center text-sm text-[#666]"
        role="alert"
      >
        {{ reviews.error }}
      </p>
      <p
        v-else-if="reviews?.configured && !reviews.reviews.length"
        class="text-center text-sm text-[#666]"
      >
        Google reviews will appear here after the Business Profile is connected.
      </p>

      <div
        v-else-if="reviews?.reviews?.length"
        class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"
      >
        <article
          v-for="(item, index) in reviews.reviews.slice(0, 6)"
          :key="`${item.author_name}-${index}`"
          class="rounded-2xl border border-[#ead9c4] bg-white p-6 shadow-[0_8px_24px_rgba(26,18,8,0.06)]"
        >
          <p class="m-0 text-[#b8863b]">
            {{ '★'.repeat(stars(item.rating)) }}
          </p>
          <p class="mt-3 text-sm leading-6 text-[#444]">
            {{ item.text }}
          </p>
          <p class="mt-4 text-sm font-semibold text-[#222]">
            {{ item.author_name }}
          </p>
          <p
            v-if="item.relative_time || formatDate(item.publish_time)"
            class="mt-1 text-xs text-[#777]"
          >
            {{ item.relative_time || formatDate(item.publish_time) }}
          </p>
        </article>
      </div>

      <div
        v-if="reviews?.maps_url"
        class="mt-10 text-center"
      >
        <a
          :href="reviews.maps_url"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex min-h-11 items-center rounded-full border border-[#b8863b] px-6 text-sm font-semibold tracking-wide text-[#8a6428] uppercase hover:bg-[#b8863b] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b8863b]"
        >
          View all reviews on Google
        </a>
      </div>
    </UContainer>
  </section>
</template>
