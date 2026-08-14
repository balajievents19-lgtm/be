<script setup lang="ts">
withDefaults(defineProps<{
  description?: string | null
  image?: string | null
  /** When false, hides the Know More CTA (e.g. on /about). */
  showCta?: boolean
}>(), {
  description: null,
  image: null,
  showCta: true
})
</script>

<template>
  <section
    class="aboutUs relative inline-block w-full bg-navy-500"
    aria-labelledby="about-heading"
  >
    <div class="relative flex w-full flex-col md:block">
      <div
        class="ourText box-border w-full px-6 py-10 text-base leading-[30px] text-white min-[640px]:px-[25px] min-[640px]:py-10 md:float-left md:w-1/2 md:px-[50px] md:py-[73px] md:pb-[68px]"
        :class="{ 'md:w-full': !image }"
      >
        <h2
          id="about-heading"
          class="mb-2.5 font-['Open_Sans',sans-serif] text-[30px] font-light leading-10 text-white min-[640px]:text-[36px] min-[640px]:leading-[46px] md:text-[40px] md:leading-[56px] lg:text-[42px] lg:leading-[70px]"
        >
          About
          <span class="font-bold font-['Domine',Georgia,'Times_New_Roman',serif]">Us</span>
        </h2>

        <p
          v-if="description"
          class="m-0 text-base leading-[30px] text-white"
        >
          {{ description }}
        </p>

        <NuxtLink
          v-if="showCta"
          to="/about"
          class="mt-[30px] inline-block border-2 border-white px-[69px] py-[18px] text-sm font-normal uppercase tracking-wide text-white transition-colors duration-300 hover:bg-white hover:text-navy-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        >
          Know More
        </NuxtLink>
      </div>

      <div
        v-if="image"
        class="ourImg relative w-full overflow-hidden md:absolute md:top-0 md:right-0 md:h-full md:w-1/2"
      >
        <img
          :src="image"
          alt="About us"
          class="block h-auto w-full object-cover md:hidden"
          width="800"
          height="600"
          loading="lazy"
          decoding="async"
        >
        <div
          class="hidden h-full min-h-[320px] w-full bg-cover bg-center md:block"
          :style="{ backgroundImage: `url('${image}')` }"
          aria-hidden="true"
        />
      </div>
    </div>
  </section>
</template>
