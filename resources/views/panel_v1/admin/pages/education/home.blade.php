@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 text-start">
            <h1 class="font-semibold text-26px sm:text-32px text-primary mb-2">{{ $welcomeTitle }}</h1>
            <p class="font-medium text-15px sm:text-16px text-gray max-w-2xl leading-relaxed">{{ $welcomeSubtitle }}</p>
        </div>
        <a href="#"
            class="inline-flex items-center justify-center h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition shrink-0">
            + إنشاء دورة جديدة
        </a>
    </div>

    @include('panel_v1.admin.components.stats-cards', ['stats' => $stats ?? []])

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 sm:gap-6 items-start">
        <div class="xl:col-span-7 space-y-5 sm:space-y-6">
            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-5 text-start">الرسم البياني للنشاط الأكاديمي</h2>
                <div class="h-52 sm:h-64 rounded-12px bg-[#FAFAF4] border border-d9 relative overflow-hidden px-4 py-6">
                    <svg viewBox="0 0 400 160" class="w-full h-full" aria-hidden="true">
                        <polyline fill="none" stroke="#0f4c45" stroke-width="3"
                            points="20,120 70,90 120,100 170,60 220,75 270,40 320,55 370,30" />
                        <circle cx="370" cy="30" r="5" fill="#C99C69" />
                    </svg>
                    <div class="absolute bottom-3 inset-x-4 flex justify-between font-medium text-11px text-gray">
                        @foreach (['أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت'] as $d)
                            <span>{{ $d }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                    @foreach ($chartMetrics ?? [] as $metric)
                        <div class="rounded-12px border border-d9 bg-[#FAFAF4] px-3 py-3 text-center">
                            <p class="font-bold text-18px text-primary mb-1">{{ $metric['value'] }}</p>
                            <p class="font-medium text-12px text-gray">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6 min-h-40">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-2 text-start">أحدث استفسارات منتديات الأسئلة</h2>
                <p class="font-medium text-14px text-gray text-start">لا توجد استفسارات حالياً.</p>
            </div>
        </div>

        <div class="xl:col-span-5 space-y-5 sm:space-y-6">
            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-5 text-start">آخر الدورات</h2>
                <div class="space-y-3">
                    @foreach ($latestCourses ?? [] as $course)
                        <article class="flex items-center gap-3 rounded-12px border border-d9 bg-[#FAFAF4] p-3">
                            <div class="size-14 rounded-10px bg-primary shrink-0"></div>
                            <div class="min-w-0 flex-1 text-start">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <p class="font-semibold text-15px text-primary truncate">{{ $course['title'] }}</p>
                                    <span @class([
                                        'shrink-0 rounded-full px-2.5 py-0.5 font-semibold text-11px',
                                        'bg-[#D1FAE5] text-[#059669]' => ($course['statusTone'] ?? '') === 'success',
                                        'bg-[#FEE2E2] text-[#DC2626]' => ($course['statusTone'] ?? '') === 'danger',
                                    ])>
                                        {{ $course['status'] }}
                                    </span>
                                </div>
                                <p class="font-medium text-13px text-gray">{{ $course['type'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6 min-h-40">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-2 text-start">الواجبات والتكليفات بانتظار التصحيح</h2>
                <p class="font-medium text-14px text-gray text-start">لا توجد تكليفات بانتظار التصحيح.</p>
            </div>
        </div>
    </div>
</div>
@endsection
