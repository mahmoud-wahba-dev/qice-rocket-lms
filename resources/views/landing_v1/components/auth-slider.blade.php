@props([
    'slides' => [
        'انضم لآلاف المتدربين الذين بدأوا رحلة تغيير مسارهم المهني معنا اليوم.',
        'انضم لآلاف المتدربين الذين بدأوا رحلة تغيير مسارهم المهني معنا اليوم.',
        'انضم لآلاف المتدربين الذين بدأوا رحلة تغيير مسارهم المهني معنا اليوم.',
    ],
])

@php($sliderImg = asset('assets/landing_v1/img/'))

<div class="">
    <div id="auth-slider"
        data-carousel='{ "loadingClasses": "opacity-0", "dotsItemClasses": "carousel-dot carousel-dot-light", "isRTL": true, "isInfiniteLoop": true, "isAutoPlay": true, "isDraggable": true }'
        class="relative w-full h-full">
        <div class="carousel h-full overflow-hidden">
            <div class="carousel-body h-full opacity-0">
                @foreach ($slides as $slideText)
                    <div class="carousel-slide relative">
                        <div class="bg-primary rounded-10px py-11 px-7 h-full relative"
                            style="background-image: url('{{ $sliderImg }}/home/hero-bg-opt.webp'); background-size: cover; background-position: right;">
                            <div class="layer bg-linear-login absolute inset-0 rounded-10px"></div>
                        </div>
                        <p
                            class="max-w-[95%] font-bold text-41px w-full text-white text-center absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                            {{ $slideText }}
                        </p>
                        <div class="absolute top-8 left-6 w-48 h-16">
                            <img src="{{ $sliderImg }}/logo-footer.svg" alt="logo" class="size-full object-cover">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="carousel-pagination absolute bottom-4 end-0 start-0 z-20 flex justify-center gap-3"></div>
    </div>
</div>
