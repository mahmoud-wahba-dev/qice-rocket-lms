@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">اختباراتي</h1>
            <p class="font-semibold text-20px text-gray">نتائجك واختباراتك المعلقة</p>
        </div>

        @if (($pendingQuizzes ?? collect())->isNotEmpty())
            <div class="bg-[#FEF6E7] border border-[#F5D9A8]/60 rounded-16px px-7 py-6 mb-10">
                <h3 class="font-bold text-18px text-primary mb-4">اختبارات بانتظار المحاولة ({{ $pendingQuizzes->count() }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($pendingQuizzes as $quiz)
                        <div class="bg-white rounded-12px border border-d9 px-5 py-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-16px text-primary truncate">{{ $quiz->title }}</p>
                                <p class="font-medium text-13px text-gray">{{ $quiz->webinar->title ?? '' }}</p>
                            </div>
                            <a href="{{ route('panel.v1.student.course.quiz', ['slug' => $quiz->webinar->slug ?? 'demo']) }}" class="btn btn-primary btn-sm rounded-8px font-bold text-13px shrink-0">ابدأ</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="border border-d9 rounded-16px bg-white overflow-hidden">
            <div class="hidden md:grid md:grid-cols-[2fr_1.2fr_0.9fr_0.9fr_1fr] gap-4 px-6 py-4 bg-fa border-b border-d9 font-bold text-14px text-gray">
                <span>الاختبار</span>
                <span class="text-center">الدورة</span>
                <span class="text-center">الدرجة</span>
                <span class="text-center">الحالة</span>
                <span class="text-center">إجراء</span>
            </div>

            @forelse ($quizResults ?? [] as $result)
                <div class="grid grid-cols-1 md:grid-cols-[2fr_1.2fr_0.9fr_0.9fr_1fr] gap-3 px-6 py-5 border-b border-d9 last:border-0 items-center">
                    <div class="min-w-0">
                        <p class="font-bold text-16px text-primary">{{ $result->quiz->title ?? '' }}</p>
                        <p class="font-medium text-12px text-gray md:hidden">{{ $result->quiz->webinar->title ?? '' }} — {{ date('Y/m/d', (int) $result->created_at) }}</p>
                    </div>
                    <p class="hidden md:block font-medium text-14px text-gray text-center">{{ $result->quiz->webinar->title ?? '' }}</p>
                    <p class="font-bold text-14px text-primary text-center"><span class="md:hidden font-medium text-gray">الدرجة: </span>{{ $result->user_grade ?? '—' }} / {{ $result->quiz->total_mark ?? $result->quiz->pass_mark ?? '—' }}</p>
                    <div class="text-center">
                        <span class="inline-flex rounded-full px-3 py-1 font-bold text-12px {{ ($result->status ?? '')==='passed' ? 'bg-[#E8F5E9] text-[#00B31B]' : (($result->status ?? '')==='waiting' ? 'bg-[#FEF6E7] text-[#D97706]' : 'bg-[#FEF2F2] text-[#EF4444]') }}">
                            {{ ($result->status ?? '')==='passed' ? 'ناجح' : (($result->status ?? '')==='waiting' ? 'بانتظار التصحيح' : 'راسب') }}
                        </span>
                    </div>
                    <div class="text-center flex items-center justify-center gap-2">
                        <span class="font-medium text-12px text-gray hidden md:block">{{ date('Y/m/d', (int) $result->created_at) }}</span>
                        @if (!empty($result->can_try))
                            <a href="{{ route('panel.v1.student.course.quiz', ['slug' => $result->quiz->webinar->slug ?? 'demo']) }}" class="font-bold text-13px text-primary hover:underline">إعادة</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-8 py-16 center flex-col text-center">
                    <p class="font-semibold text-20px text-gray">لا توجد نتائج اختبارات بعد</p>
                    <p class="font-medium text-14px text-gray mt-2">ادخل إلى دوراتك وابدأ أول اختبار.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
