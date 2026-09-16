@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $statusToneClass = [
        'success' => 'bg-[#ECFDF5] text-[#059669]',
        'warning' => 'bg-[#FFFBEB] text-[#D97706]',
        'danger' => 'bg-[#FEF2F2] text-[#DC2626]',
        'muted' => 'bg-[#F1F5F9] text-[#64748B]',
    ];
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'تعليقات الدورات',
        'subtitle' => 'متابعة تعليقات الطلاب على دوراتك والرد عليها',
    ])
    @endcomponent

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($commentStats ?? [] as $stat)
            <div class="rounded-14px bg-primary text-white px-5 py-5 flex items-center gap-4 min-h-[100px]">
                <span class="icon-[tabler--message] size-7 text-color2 shrink-0"></span>
                <div>
                    <p class="font-semibold text-26px sm:text-28px leading-none mb-1.5">{{ $stat['value'] }}</p>
                    <p class="font-semibold text-14px sm:text-15px text-white/90 leading-snug">{{ $stat['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-d9 p-4 sm:p-5 rounded-14px">
        <form method="GET" action="{{ route('panel.v1.instructor.comments') }}" class="flex flex-col sm:flex-row gap-3">
            <select name="course_id"
                class="select select-bordered h-12 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary flex-1 min-w-[14rem]">
                <option value="">كل الدورات</option>
                @foreach ($courseOptions ?? [] as $course)
                    <option value="{{ $course['id'] }}" @selected(($selectedCourseId ?? null) == $course['id'])>
                        {{ $course['title'] }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-10px bg-primary px-5 h-12 font-semibold text-15px text-white hover:opacity-95 transition">
                تطبيق
            </button>
        </form>
    </div>

    @if (empty($commentRows))
        <div class="border border-d9 rounded-14px bg-white p-10 text-center">
            <span class="icon-[tabler--message-off] size-12 text-gray mx-auto mb-3 block"></span>
            <p class="font-semibold text-18px text-primary mb-1">لا توجد تعليقات بعد</p>
            <p class="font-medium text-14px text-gray">ستظهر هنا تعليقات الطلاب على دوراتك فور إضافتها.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($commentRows as $row)
                <article class="border border-d9 rounded-14px bg-white p-5 sm:p-6 shadow-sm" id="comment-{{ $row['id'] }}">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="size-12 rounded-full bg-primary/10 center shrink-0 overflow-hidden">
                                @if (!empty($row['avatar']))
                                    <img src="{{ $row['avatar'] }}" alt="" class="size-full object-cover">
                                @else
                                    <span class="font-bold text-16px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-17px text-primary">{{ $row['name'] }}</h3>
                                    <span class="inline-flex rounded-full px-3 py-1 font-semibold text-12px {{ $statusToneClass[$row['status_tone'] ?? 'success'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </div>
                                <p class="font-medium text-14px text-gray">
                                    {{ $row['course'] }}
                                    <span class="mx-1.5 text-d9">·</span>
                                    {{ $row['date'] }} {{ $row['time'] }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ $row['course_url'] }}"
                                class="inline-flex items-center gap-1.5 rounded-10px border border-d9 px-3 h-10 font-semibold text-13px text-primary hover:bg-fa transition">
                                عرض الدورة
                            </a>
                            <button type="button"
                                class="inline-flex items-center gap-1.5 rounded-10px bg-primary px-3 h-10 font-semibold text-13px text-white hover:opacity-95 transition"
                                data-comment-reply-toggle="{{ $row['id'] }}">
                                <span class="icon-[tabler--message-2] size-4"></span>
                                رد
                            </button>
                        </div>
                    </div>

                    <div class="rounded-12px bg-[#F8FAFC] border border-d9 px-4 py-3.5 mb-4">
                        <p class="font-medium text-15px text-primary leading-relaxed whitespace-pre-line">{{ $row['body'] }}</p>
                    </div>

                    @if (!empty($row['replies']))
                        <div class="space-y-3 mb-4 ms-0 sm:ms-6 border-s-2 border-primary/20 ps-4">
                            @foreach ($row['replies'] as $reply)
                                <div class="rounded-12px bg-primary/5 border border-primary/15 px-4 py-3">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <p class="font-semibold text-14px text-primary">
                                        {{ $reply['name'] }}
                                        @if (!empty($reply['is_instructor']))
                                            <span class="font-medium text-12px text-gray">(رد المدرب)</span>
                                        @endif
                                    </p>
                                        <span class="font-medium text-12px text-gray">{{ $reply['date'] }}</span>
                                    </div>
                                    <p class="font-medium text-14px text-primary leading-relaxed">{{ $reply['body'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="hidden border border-d9 rounded-12px p-4 bg-white" data-comment-reply-box="{{ $row['id'] }}">
                        <form method="POST" action="{{ $row['reply_url'] }}" class="space-y-3">
                            @csrf
                            @if (!empty($selectedCourseId))
                                <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                            @endif
                            <label class="block font-semibold text-14px text-primary">ردك على {{ $row['name'] }}</label>
                            <textarea name="comment" rows="3" required maxlength="5000"
                                class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px text-primary min-h-24"
                                placeholder="اكتب ردك هنا..."></textarea>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button type="button" class="btn btn-ghost rounded-10px h-11 px-4 font-semibold text-14px text-gray"
                                    data-comment-reply-toggle="{{ $row['id'] }}">إلغاء</button>
                                <button type="submit" class="btn btn-primary rounded-10px h-11 px-5 font-bold text-14px">إرسال الرد</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ $row['report_url'] }}" class="mt-3 flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="text" name="message" required maxlength="2000"
                                placeholder="سبب البلاغ"
                                class="input input-bordered h-11 rounded-10px border-d9 font-medium text-14px flex-1">
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-10px bg-[#FEF3C7] px-4 h-11 font-semibold text-13px text-[#B45309] shrink-0">
                                إبلاغ عن التعليق
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

<script>
(() => {
    document.querySelectorAll('[data-comment-reply-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-comment-reply-toggle');
            const box = document.querySelector(`[data-comment-reply-box="${id}"]`);
            if (!box) return;
            box.classList.toggle('hidden');
            if (!box.classList.contains('hidden')) {
                box.querySelector('textarea')?.focus();
            }
        });
    });
})();
</script>
@endsection
