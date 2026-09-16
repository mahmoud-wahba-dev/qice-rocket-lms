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
                        $bundle = $certificate->bundle ?? null;
                        $quiz = $certificate->quiz ?? null;
                        if ($certificate->type === 'quiz' && $quiz) {
                            $title = $quiz->title ?? 'شهادة اختبار #' . $certificate->id;
                            $type = 'شهادة اختبار';
                            $category = $quiz->webinar->category->title ?? '';
                        } elseif ($certificate->type === 'bundle' && $bundle) {
                            $title = $bundle->title ?? 'شهادة حزمة #' . $certificate->id;
                            $type = 'شهادة حزمة';
                            $category = '';
                        } else {
                            $title = $webinar->title ?? ($quiz->title ?? 'شهادة #' . $certificate->id);
                            $type = 'إتمام دورة';
                            $category = $webinar->category->title ?? '';
                        }
                        $validationUrl = url('/certificate_validation?certificate_id=' . $certificate->id);
                    @endphp
                    <article class="rounded-16px border border-d9 bg-white p-6 flex flex-col gap-5 hover:shadow-md transition">
                        <div class="bg-primary h-44 rounded-12px center relative overflow-hidden">
                            <span class="icon-[tabler--certificate] size-14 text-white"></span>
                            <span class="absolute top-3 end-3 rounded-full bg-white/20 px-3 py-1 font-bold text-12px text-white">{{ $type }}</span>
                            <span class="absolute bottom-2 start-3 rounded-full bg-white/15 px-2.5 py-1 font-mono font-bold text-11px text-white">#{{ $certificate->id }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-18px text-primary mb-1 leading-snug line-clamp-2">{{ $title }}</h3>
                            @if (!empty($category))
                                <p class="font-medium text-13px text-gray">{{ $category }}</p>
                            @endif
                            <p class="font-medium text-12px text-[#00B31B] mt-2">اكتمل في {{ date('Y/m/d', (int) $certificate->created_at) }}</p>
                            <a href="{{ $validationUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 mt-2 font-semibold text-11px text-primary hover:underline">
                                <span class="icon-[tabler--shield-check] size-3.5"></span> تحقق: {{ $validationUrl }}
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('panel.v1.student.certificates.download', ['id' => $certificate->id]) }}"
                                class="btn btn-primary rounded-10px h-11 flex-1 font-bold text-13px gap-2">
                                <span class="icon-[tabler--download] size-4"></span>
                                تحميل PDF
                            </a>
                            <a href="{{ route('panel.v1.student.certificates.download', ['id' => $certificate->id, 'view' => 1]) }}"
                                target="_blank" rel="noopener"
                                class="btn btn-ghost rounded-10px h-11 px-4 font-bold text-13px text-primary border border-d9">
                                عرض
                            </a>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-d9/60">
                            <span class="font-medium text-11px text-gray">معرّف: #{{ $certificate->id }}</span>
                            <a href="{{ $validationUrl }}" target="_blank" class="font-bold text-11px text-primary hover:underline flex items-center gap-1">
                                <span class="icon-[tabler--qrcode] size-3.5"></span> QR تحقق
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8 rounded-12px bg-[#F0FDF4] border border-[#BBF7D0] px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                <p class="font-semibold text-13px text-[#065F46] flex items-center gap-2">
                    <span class="icon-[tabler--info-circle] size-4"></span>
                    جميع الشهادات تحمل QR للتحقق الفوري عبر <a href="{{ url('/certificate_validation') }}" target="_blank" class="underline font-bold">صفحة التحقق</a>
                </p>
                <span class="font-medium text-12px text-gray">تُحفظ الشهادات كـ PDF مع خط Vazir لدعم العربية + توقيع المدرب</span>
            </div>
        @endif
    </div>
</section>
@endsection
