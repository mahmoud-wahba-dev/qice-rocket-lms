<footer class="bg-primary text-white pt-13 pb-4">
    <div class="container">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-6 lg:gap-8 max-sm:gap-8">
            <div class="sm:col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 text-xl font-bold mb-4 h-32">
                    <img src="{{ $landingImg }}/logo-footer.webp" alt="QIEC Training" class="h-full" width=""
                        height="" loading="lazy" decoding="async"/>
                </div>
                <p class="font-normal text-16px mb-4 text-white lg:w-[90%]">
                    مركز الجودة والتميز للتدريب مؤسسة متخصصة في تقديم التدريب النوعي الذي يركز على التطبيق العملي لتلبية
                    الاحتياجات التدريبية للمؤسسات والأفراد.
                </p>
                <div class="flex items-center gap-4 xl:flex-nowrap flex-wrap">
                    <img src="{{ $landingImg }}/logo-footer4.webp" alt="QIEC accreditation" class="w-72" width="288"
                        height="80" loading="lazy" decoding="async">
                    <img src="{{ $landingImg }}/logo-footer2.webp" alt="QIEC partner" class="w-72" width="288"
                        height="80" loading="lazy" decoding="async">
                </div>
            </div>

            <nav class="flex justify-center max-sm:justify-start">
                <div>
                    <h6 class="footer-title font-semibold text-24px text-white mb-4">روابط سريعة</h6>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('landing.v1.index') }}"
                            class="link link-hover font-normal text-19px text-white">الرئيسية</a>
                        <a href="{{ route('landing.v1.workshops') }}"
                            class="link link-hover font-normal text-19px text-white">الدورات المجانية</a>
                        <a href="{{ route('landing.v1.courses-paid') }}"
                            class="link link-hover font-normal text-19px text-white">الدورات المعتمدة</a>
                        <a href="{{ route('landing.v1.workshops') }}"
                            class="link link-hover font-normal text-19px text-white">الخطة التدريبية</a>
                    </div>
                </div>
            </nav>

            <nav>
                <h6 class="footer-title font-semibold text-24px text-white mb-4">تصنيف الدورات</h6>
                <div class="flex flex-col gap-2">
                    @php
                        $footerCategories = $footerCategories ?? \Illuminate\Support\Facades\Cache::remember(
                            'landing_v1.footer_categories',
                            now()->addMinutes(30),
                            fn () => \App\Models\Category::whereNull('parent_id')
                                ->where('enable', true)
                                ->orderBy('order')
                                ->limit(6)
                                ->get()
                        );
                    @endphp
                    @forelse ($footerCategories as $category)
                        <a href="{{ route('landing.v1.courses-paid', ['category_id' => $category->id]) }}"
                            class="link link-hover font-normal text-19px text-white">{{ $category->title }}</a>
                    @empty
                        <a href="{{ route('landing.v1.courses-paid') }}"
                            class="link link-hover font-normal text-19px text-white">الدورات المعتمدة</a>
                    @endforelse
                </div>
            </nav>

            <nav>
                <h6 class="footer-title font-semibold text-24px text-white mb-4">التواصل</h6>
                <p class="link link-hover font-normal text-19px text-white">
                    للتسجيل والاستفسارات، يرجى التواصل معنا
                </p>
                <a href="mailto:contact@training.qiec.sa"
                    class="link link-hover font-normal text-19px text-white block mt-2">
                    contact@training.qiec.sa
                </a>
                @include('landing_v1.components.social-links')
            </nav>
        </div>

        <div class="footer text-white border-[#FFFFFF5E] border-t px-6 py-5 mt-9">
            <div class="center w-full">
                <aside class="grid-flow-col items-center">
                    <p class="font-normal text-white text-17px text-center">
                        Quality & Excellence Center for Training
                    </p>
                </aside>
            </div>
        </div>
    </div>
</footer>
