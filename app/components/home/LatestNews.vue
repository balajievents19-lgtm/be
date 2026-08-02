<script setup lang="ts">
import { latestNews } from '~/data/home'

const featured = latestNews.find(item => item.variant === 'featured')!
const textCards = latestNews.filter(item => item.variant === 'text')
const sideCard = latestNews.find(item => item.variant === 'side')!
</script>

<template>
  <section
    class="news-view bg-[#f5f5f6] py-10 pb-[30px]"
    aria-labelledby="latest-news-heading"
  >
    <UContainer class="mx-auto max-w-[1170px]">
      <div class="mb-0 w-full pb-[90px] text-center">
        <div class="block w-full">
          <em
            class="icon icon-heading-icon inline-block h-10 align-top text-[56px] leading-none text-[#f15b22]"
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
            class="relative z-[2] m-0 inline-block bg-[#f5f5f6] px-5 text-center text-2xl font-bold leading-8 text-[#333333] font-['Domine',Georgia,'Times_New_Roman',serif]"
          >
            Latest News
          </h2>
        </div>

        <p class="mx-auto m-0 inline-block w-full max-w-[761px] text-center text-sm leading-7 text-[#6a6767]">
          It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
        </p>
      </div>

      <div class="-mx-[15px] flex flex-wrap">
        <div class="w-full px-[15px] min-[992px]:w-2/3">
          <article class="news-box mb-[30px] bg-white">
            <div class="-mx-[15px] flex flex-wrap">
              <div class="w-full px-[15px] min-[768px]:w-1/2">
                <img
                  :src="featured.image"
                  :alt="featured.title"
                  class="block h-auto w-full"
                  loading="lazy"
                  decoding="async"
                >
              </div>
              <div class="w-full px-[15px] min-[768px]:w-1/2">
                <div class="px-5 pt-[35px] pb-11 max-[1199px]:py-[15px] max-[767px]:p-[30px]">
                  <div class="relative mb-[34px] pb-[15px] max-[1199px]:mb-2.5">
                    <h3 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333]">
                      {{ featured.title }}
                    </h3>
                    <span class="block text-sm leading-6 text-[#666]">
                      {{ featured.author }} on {{ featured.date }}
                    </span>
                    <span
                      class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                      aria-hidden="true"
                    />
                  </div>
                  <p class="m-0 w-full max-w-[280px] pb-6 text-sm leading-6 text-[#666]">
                    {{ featured.excerpt }}
                  </p>
                  <NuxtLink
                    :to="featured.to"
                    class="inline-block rounded-[3px] border border-solid border-[#f15b22] bg-[#f15b22] px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                  >
                    Read More
                  </NuxtLink>
                </div>
              </div>
            </div>
          </article>

          <div class="-mx-[15px] flex flex-wrap">
            <div
              v-for="(card, index) in textCards"
              :key="`news-text-${index}`"
              class="w-full px-[15px] min-[768px]:w-1/2"
            >
              <article class="news-box style2 mb-[30px] bg-white">
                <div class="p-10 max-[767px]:p-[30px]">
                  <div class="relative mb-[34px] pb-[15px] max-[1199px]:mb-2.5">
                    <h3 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333]">
                      {{ card.title }}
                    </h3>
                    <span class="block text-sm leading-6 text-[#666]">
                      {{ card.author }} on {{ card.date }}
                    </span>
                    <span
                      class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                      aria-hidden="true"
                    />
                  </div>
                  <p class="m-0 w-full max-w-[280px] pb-6 text-sm leading-6 text-[#666]">
                    {{ card.excerpt }}
                  </p>
                  <NuxtLink
                    :to="card.to"
                    class="inline-block rounded-[3px] border border-solid border-[#f15b22] bg-[#f15b22] px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
                  >
                    Read More
                  </NuxtLink>
                </div>
              </article>
            </div>
          </div>
        </div>

        <div class="w-full px-[15px] min-[992px]:w-1/3">
          <article class="news-box style3 mb-[30px] overflow-hidden bg-white">
            <div class="max-[991px]:overflow-hidden max-[767px]:block">
              <img
                :src="sideCard.image"
                :alt="sideCard.title"
                class="block h-auto w-full max-[991px]:float-left max-[991px]:w-1/2 max-[991px]:pr-[15px] max-[767px]:float-none max-[767px]:w-full max-[767px]:pr-0"
                loading="lazy"
                decoding="async"
              >
              <div
                class="px-[50px] pt-[50px] pb-[60px] max-[991px]:float-left max-[991px]:w-1/2 max-[991px]:pt-[30px] max-[767px]:float-none max-[767px]:w-full max-[767px]:p-[30px]"
              >
                <div class="relative mb-[34px] pb-[15px] max-[1199px]:mb-2.5">
                  <h3 class="m-0 block font-['Domine',Georgia,'Times_New_Roman',serif] text-2xl font-bold leading-9 text-[#333333]">
                    {{ sideCard.title }}
                  </h3>
                  <span class="block text-sm leading-6 text-[#666]">
                    {{ sideCard.author }} on {{ sideCard.date }}
                  </span>
                  <span
                    class="absolute bottom-0 left-0 h-px w-16 bg-[#cccccc]"
                    aria-hidden="true"
                  />
                </div>
                <p class="m-0 w-full max-w-[280px] pb-6 text-sm leading-6 text-[#666]">
                  {{ sideCard.excerpt }}
                </p>
                <NuxtLink
                  :to="sideCard.to"
                  class="inline-block rounded-[3px] border border-solid border-[#f15b22] bg-[#f15b22] px-7 py-[9px] text-center text-lg leading-5 text-white shadow-[inset_0_1px_0_#e0a97f] transition-colors duration-1000 hover:bg-[#e7480b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f15b22]"
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
