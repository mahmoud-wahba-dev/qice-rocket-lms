@props([
    'title',
    'subtitle',
])

<section class="pt-8 lg:pt-25 pb-20 my-0 relative overflow-visible">
    <div
        class="pointer-events-none absolute left-1/2 top-[70%] z-0 size-[297px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#FFF1D5] blur-glow-circle"
        aria-hidden="true">
    </div>

    <div class="container relative z-10">
        <h2 class="font-bold text-38px text-primary mb-4 text-center">{{ $title }}</h2>
        <p class="font-normal text-base text-primary/70 text-center max-w-4xl mx-auto">{{ $subtitle }}</p>
    </div>
</section>
