@extends('landing_v1.layouts.app')

@section('content')
    <main>
    <section class="pt-8 lg:pt-20 pb-20 my-0 relative overflow-visible">
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

<div class="container mb-8">
    <nav class="tabs bg-base-200 rounded-btn w-fit space-x-1 overflow-x-auto p-1 rtl:space-x-reverse" aria-label="Tabs" role="tablist" aria-orientation="horizontal" >
        <button type="button" class="btn btn-text active-tab:bg-primary active-tab:text-white hover:text-primary active hover:bg-transparent" id="tabs-segment-item-1" data-tab="#tabs-segment-1" aria-controls="tabs-segment-1" role="tab" aria-selected="true" >
            الكل
        </button>
        <button type="button" class="btn btn-text active-tab:bg-primary active-tab:text-white hover:text-primary hover:bg-transparent" id="tabs-segment-item-2" data-tab="#tabs-segment-2" aria-controls="tabs-segment-2" role="tab" aria-selected="false" >
            الإدارة
        </button>
        <button type="button" class="btn btn-text active-tab:bg-primary active-tab:text-white hover:text-primary hover:bg-transparent" id="tabs-segment-item-3" data-tab="#tabs-segment-3" aria-controls="tabs-segment-3" role="tab" aria-selected="false" >
            التسويق
        </button>
      </nav>
      
        {{-- <div class="mt-3 ">
            <div id="tabs-segment-1" role="tabpanel" aria-labelledby="tabs-segment-item-1">
            <p class="text-base-content/80">
                Welcome to the <span class="text-base-content font-semibold">Home tab!</span> Explore the latest updates and news here.
            </p>
            </div>
            <div id="tabs-segment-2" class="hidden" role="tabpanel" aria-labelledby="tabs-segment-item-2">
            <p class="text-base-content/80">
                This is your <span class="text-base-content font-semibold">Profile</span> tab, where you can update your personal information and manage your account details.
            </p>
            </div>
            <div id="tabs-segment-3" class="hidden" role="tabpanel" aria-labelledby="tabs-segment-item-3">
            <p class="text-base-content/80">
                <span class="text-base-content font-semibold">Messages:</span> View your recent messages, chat with friends, and manage your conversations.
            </p>
            </div>
        </div> --}}
</div>


        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @for ($i = 0; $i < 12; $i++)
                        <x-landing_v1::course-card />
                    @endfor
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
