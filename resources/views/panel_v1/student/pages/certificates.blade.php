@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-extrabold text-36px text-primary mb-3">شهاداتي</h1>
                <p class="font-semibold text-20px text-gray">شهادات الإتمام والاختبارات التي حصلت عليها</p>
            </div>
            @if (!empty($pendingCount) && $pendingCount > 0)
                <div class="rounded-12px bg-[#FEF6E7] border border-[#F5D9A8]/60 px-5 py-3">
                    <p class="font-bold text-14px text-[#7A4A00]">{{ $pendingCount }} شهادة بانتظار الإكمال</p>
                </div>
            @endif
        </div>

        @if (($certificates ?? collect())->isEmpty())
            <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                <div class="size-20 rounded-full bg-primary/10 center mb-6">
                    <span class="icon-[tabler--certificate] size-10 text-primary"></span>
                </div>
                <p class="font-bold text-24px text-primary mb-2">لا توجد شهادات بعد</p>
                <p class="font-medium text-16px text-gray mb-6">أكمل دوراتك واجتز اختباراتها للحصول على الشهادات المعتمدة.</p>
                <a href="{{ route('panel.v1.student.home') }}" class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px">العودة للوحة</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($certificates as $certificate)
                    @php
                        $webinar = $certificate->webinar ?? null;
                        $quiz = $certificate->quiz ?? null;
                        $title = $webinar->title ?? ($quiz->title ?? 'شهادة #' . $certificate->id);
                        $type = !empty($certificate->quiz_id) ? 'اختبار' : 'إتمام دورة';
                    @endphp
                    <article class="rounded-16px border border-d9 bg-white p-6 flex flex-col gap-5">
                        <div class="bg-primary h-44 rounded-12px center relative overflow-hidden">
                            <span class="icon-[tabler--certificate] size-14 text-white"></span>
                            <span class="absolute top-3 end-3 rounded-full bg-white/20 px-3 py-1 font-bold text-12px text-white">{{ $type }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-20px text-primary mb-1 leading-snug">{{ $title }}</h3>
                            @if (!empty($webinar))
                                <p class="font-medium text-14px text-gray">{{ $webinar->category->title ?? '' }}</p>
                            @endif
                            <p class="font-medium text-13px text-[#00B31B] mt-2">اكتمل في {{ date('Y/m/d', (int) $certificate->created_at) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('panel.v1.student.certificates.download', ['id' => $certificate->id]) }}"
                                class="btn btn-primary rounded-10px h-11 flex-1 font-bold text-14px">تحميل الشهادة</a>
                            @if (!empty($webinar))
                                <a href="{{ route('panel.v1.student.course.watch', ['slug' => $webinar->slug]) }}"
                                    class="btn btn-ghost rounded-10px h-11 px-4 font-bold text-14px text-primary">عرض الدورة</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
