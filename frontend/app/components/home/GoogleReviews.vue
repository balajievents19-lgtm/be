<script setup lang="ts">
import type { GoogleReviewsPayload, TestimonialItem } from '~/types/home'

const GOOGLE_PLACE_ID_FALLBACK = 'ChIJBymY3mE5EzkRsExzAapRL1o'

const props = withDefaults(defineProps<{
  reviews?: GoogleReviewsPayload | null
  featuredReviews?: TestimonialItem[]
  pending?: boolean
  failed?: boolean
}>(), {
  reviews: null,
  featuredReviews: () => [],
  pending: false,
  failed: false
})

const featuredCards = computed(() => (props.featuredReviews ?? []).slice(0, 4))

const mapsUrl = computed(() => {
  const configured = props.reviews?.maps_url?.trim()
  if (configured) {
    return configured
  }

  return `https://www.google.com/maps/place/?q=place_id:${GOOGLE_PLACE_ID_FALLBACK}`
})

const showSection = computed(() => {
  if (props.pending) {
    return true
  }
  if (props.reviews?.configured || props.reviews?.rating || mapsUrl.value) {
    return true
  }
  return featuredCards.value.length > 0
})

const stars = (rating: number | null | undefined) => {
  const value = Math.round(Number(rating || 0))
  return Math.min(5, Math.max(0, value))
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
            class="block text-[#666] min-[480px]:ml-2 min-[480px]:inline"
          >{{ reviews.review_count }} Google Reviews</span>
        </p>
      </div>

      <p
        v-if="failed && !reviews?.rating && !featuredCards.length"
        class="text-center text-sm text-[#666]"
        role="alert"
      >
        Google reviews are temporarily unavailable.
      </p>
      <p
        v-else-if="pending && !featuredCards.length && !reviews?.rating"
        class="text-center text-sm text-[#666]"
        role="status"
      >
        Loading Google reviews…
      </p>

      <div
        v-if="featuredCards.length"
        class="grid grid-cols-1 gap-5 min-[768px]:grid-cols-2"
      >
        <article
          v-for="item in featuredCards"
          :key="item.id"
          class="rounded-2xl border border-[#ead9c4] bg-white p-6 shadow-[0_8px_24px_rgba(26,18,8,0.06)]"
        >
          <p class="m-0 text-xs font-semibold tracking-[0.16em] text-[#b8863b] uppercase">
            Featured Customer Review
          </p>
          <p
            v-if="item.rating"
            class="mt-2 text-[#b8863b]"
          >
            {{ '★'.repeat(stars(item.rating)) }}
          </p>
          <p class="mt-3 text-sm leading-6 text-[#444]">
            {{ item.quote }}
          </p>
          <p class="mt-4 text-sm font-semibold text-[#222]">
            {{ item.name }}
          </p>
        </article>
      </div>

      <div
        v-if="mapsUrl"
        class="mt-10 text-center"
      >
        <a
          :href="mapsUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex min-h-11 items-center rounded-full border border-[#b8863b] px-6 text-sm font-semibold tracking-wide text-[#8a6428] uppercase hover:bg-[#b8863b] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b8863b]"
        >
          View More Google Reviews →
        </a>
      </div>
    </UContainer>
  </section>
</template>
