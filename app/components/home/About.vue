<template>
  <section
    class="relative w-full bg-[#0e1123]"
    aria-labelledby="about-heading"
  >
    <div class="relative flex w-full flex-col md:block">
      <div
        class="box-border w-full px-6 py-10 text-base leading-[30px] text-white min-[640px]:px-[25px] min-[640px]:py-10 md:float-left md:w-1/2 md:px-[50px] md:py-[73px] md:pb-[68px]"
      >
        <h2
          id="about-heading"
          class="mb-2.5 font-['Open_Sans',sans-serif] text-[30px] font-light leading-10 text-white min-[640px]:text-[36px] min-[640px]:leading-[46px] md:text-[40px] md:leading-[56px] lg:text-[42px] lg:leading-[70px]"
        >
          About
          <span class="font-bold font-['Domine',Georgia,'Times_New_Roman',serif]">Us</span>
        </h2>

        <p class="m-0 text-base leading-[30px] text-white">
          Balaji Events is trusted wedding management company in india. We were Provide many differnt service in Rajasthan. We promise to set the perfect pitch for to-be and groom to celebrate their special day in grand way.
        </p>

        <NuxtLink
          to="/about"
          class="mt-[30px] inline-block border-2 border-white px-[69px] py-[18px] text-sm font-normal uppercase tracking-wide text-white transition-colors duration-300 hover:bg-white hover:text-[#0e1123] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
        >
          Know More
        </NuxtLink>
      </div>

      <div
        class="relative w-full overflow-hidden md:absolute md:top-0 md:right-0 md:h-full md:w-1/2"
      >
        <img
          src="/images/about-us/aboutUs.jpg"
          alt="Balaji Events about us"
          class="block h-auto w-full object-cover md:absolute md:inset-0 md:h-full"
          loading="lazy"
          decoding="async"
        >
      </div>
    </div>
  </section>
</template>
