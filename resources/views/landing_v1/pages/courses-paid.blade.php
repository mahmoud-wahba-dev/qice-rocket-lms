@extends('landing_v1.layouts.app')

@section('content')
    <main>
    <section class="pt-8 lg:pt-25 pb-20 my-0 relative overflow-visible">
    <!-- <div
        class="pointer-events-none absolute left-1/2 top-[70%] z-0 size-[297px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#FFF1D5] blur-glow-circle"
        aria-hidden="true">
    </div> -->

    <div class="container relative z-10">
        <h2 class="font-bold text-38px text-primary mb-4 text-center">الدورات المعتمدة والبرامج المدفوعة</h2>
        <p class="font-normal text-base text-primary/70 text-center max-w-4xl mx-auto">
        برامج تدريبية احترافية ومعتمدة في مختلف المجالات
        </p>
    </div>
</section>


        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @for ($i = 0; $i < 12; $i++)
                        <x-landing_v1::workshop-card />
                    @endfor
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
