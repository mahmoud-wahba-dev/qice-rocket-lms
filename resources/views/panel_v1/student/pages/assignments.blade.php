@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">تكليفاتي</h1>
            <p class="font-semibold text-20px text-gray">تابع التكليفات المعلقة والمسلمة ودرجاتك</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8">
                <div class="bg-[#F9F5F5] rounded-12px px-7 py-4 mb-6">
                    <p class="font-bold text-20px text-primary">تكليفات بانتظار التسليم ({{ ($pendingAssignments ?? collect())->count() }})</p>
                </div>

                @forelse ($pendingAssignments ?? [] as $assignment)
                    <div class="border border-d9 rounded-16px bg-white px-7 py-6 mb-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-bold text-18px text-primary mb-1">{{ $assignment->title ?? 'تكليف' }}</p>
                            <p class="font-medium text-14px text-gray">{{ $assignment->webinar->title ?? '' }} — الدرجة: {{ $assignment->grade ?? '—' }} / النجاح: {{ $assignment->pass_grade ?? '—' }}</p>
                            @if (!empty($assignment->deadline))
                                <p class="font-medium text-12px text-[#EF4444] mt-1">الموعد النهائي: {{ date('Y/m/d', (int) $assignment->deadline) }}</p>
                            @endif
                        </div>
                        <a href="{{ route('panel.v1.student.course.assignment', ['slug' => $assignment->webinar->slug ?? 'demo']) }}"
                            class="btn btn-primary rounded-10px h-11 px-6 font-bold text-14px shrink-0">تقديم الحل</a>
                    </div>
                @empty
                    <div class="border border-dashed border-d9 rounded-16px bg-fa/50 px-8 py-12 center flex-col text-center mb-8">
                        <p class="font-semibold text-18px text-gray">لا توجد تكليفات معلقة</p>
                        <p class="font-medium text-14px text-gray mt-1">أنت منجز — تابع دوراتك.</p>
                    </div>
                @endforelse

                <div class="bg-[#F9F5F5] rounded-12px px-7 py-4 mb-6 mt-10">
                    <p class="font-bold text-20px text-primary">سجل التسليمات</p>
                </div>
                @forelse ($histories ?? [] as $history)
                    <div class="border border-d9 rounded-16px bg-white px-7 py-5 mb-4">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <p class="font-bold text-16px text-primary">{{ $history->assignment->title ?? ($history->assignment->webinar->title ?? 'تكليف') }}</p>
                            <span class="rounded-full px-3 py-1 font-bold text-12px {{ ($history->status ?? '')==='passed' ? 'bg-[#E8F5E9] text-[#00B31B]' : (($history->status ?? '')==='not_passed' ? 'bg-[#FEF2F2] text-[#EF4444]' : 'bg-[#FEF6E7] text-[#D97706]') }}">
                                {{ ($history->status ?? '')==='passed' ? 'ناجح' : (($history->status ?? '')==='not_passed' ? 'راسب' : 'بانتظار التقييم') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-6 font-medium text-14px text-gray">
                            <span>الدورة: {{ $history->assignment->webinar->title ?? '' }}</span>
                            <span>الدرجة: {{ $history->grade ?? '—' }} / {{ $history->assignment->grade ?? '—' }}</span>
                            <span>{{ date('Y/m/d', (int) $history->created_at) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="font-medium text-16px text-gray">لا توجد تسليمات سابقة.</p>
                @endforelse
            </div>

            <div class="lg:col-span-4">
                <div class="border border-d9 rounded-16px bg-white p-6">
                    <h3 class="font-bold text-18px text-primary mb-4">ملخص سريع</h3>
                    <div class="space-y-3 font-medium text-15px">
                        <div class="flex justify-between"><span class="text-gray">إجمالي التكليفات</span><span class="font-bold text-primary">{{ ($myAssignments ?? collect())->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-gray">معلقة</span><span class="font-bold text-[#D97706]">{{ ($pendingAssignments ?? collect())->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-gray">مسلمة</span><span class="font-bold text-[#00B31B]">{{ ($histories ?? collect())->count() }}</span></div>
                    </div>
                </div>
                <div class="mt-6 border border-d9 rounded-16px bg-[#FAFAF4] p-6">
                    <p class="font-semibold text-15px text-primary">نصيحة</p>
                    <p class="font-medium text-14px text-gray mt-2 leading-relaxed">التزم بالمواعيد النهائية واحرص على رفع ملفاتك بصيغة PDF لضمان التصحيح السريع.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
