@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $meta = $bundleMeta ?? [];
    $courses = $bundleCourses ?? [];
    $available = $availableCourses ?? [];
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-6 py-5';
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'دورات الحزمة',
        'subtitle' => '“' . ($meta['title'] ?? '') . '”',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.bundles') }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px border border-d9 font-semibold text-15px text-primary hover:bg-fa transition">كل الحزم</a>
            <a href="{{ $meta['edit_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px border border-d9 font-semibold text-15px text-primary hover:bg-fa transition">تعديل</a>
            <a href="{{ $meta['preview_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px bg-color2 font-semibold text-15px text-white hover:opacity-95 transition">عرض عام</a>
        @endslot
    @endcomponent

    <section class="{{ $card }}">
        <h2 class="font-bold text-18px text-primary mb-4">إضافة دورة للحزمة</h2>
        @if (!empty($available))
            <form method="POST" action="{{ route('panel.v1.instructor.bundles.courses.attach', ['id' => $meta['id'] ?? 0]) }}"
                class="flex flex-col sm:flex-row gap-3">
                @csrf
                <select name="webinar_id" required
                    class="select select-bordered flex-1 h-12 rounded-10px border-d9 font-medium text-15px">
                    <option value="">اختر دورة...</option>
                    @foreach ($available as $course)
                        <option value="{{ $course['id'] }}">{{ $course['title'] }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-semibold text-15px hover:opacity-90 transition shrink-0">
                    <span class="icon-[tabler--plus] size-4"></span>
                    إضافة
                </button>
            </form>
        @else
            <p class="font-medium text-14px text-gray">كل دوراتك المفعّلة مضافة بالفعل، أو لا توجد دورات متاحة.</p>
        @endif
    </section>

    @if (!empty($courses))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($courses as $course)
                <article class="{{ $card }} flex gap-4 items-start">
                    <div class="size-16 sm:size-20 rounded-12px bg-fa overflow-hidden shrink-0">
                        @if (!empty($course['thumbnail']))
                            <img src="{{ $course['thumbnail'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full center"><span class="icon-[tabler--book] size-7 text-primary/30"></span></div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1 text-start">
                        <h3 class="font-bold text-16px text-primary mb-1 line-clamp-2">{{ $course['title'] }}</h3>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 font-medium text-13px text-gray mb-3">
                            <span>{{ $course['category'] }}</span>
                            <span>{{ $course['price_label'] }}</span>
                            <span>{{ $course['status'] }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if (!empty($course['watch_url']))
                                <a href="{{ $course['watch_url'] }}"
                                    class="inline-flex items-center gap-1.5 h-9 px-3 rounded-10px border border-d9 font-semibold text-13px text-primary hover:bg-fa transition">
                                    فتح الدورة
                                </a>
                            @endif
                            <form method="POST"
                                action="{{ route('panel.v1.instructor.bundles.courses.detach', ['id' => $meta['id'], 'webinarId' => $course['id']]) }}"
                                onsubmit="return confirm('إزالة هذه الدورة من الحزمة؟');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 h-9 px-3 rounded-10px border border-[#FECACA] font-semibold text-13px text-[#E11D48] hover:bg-[#FEF2F2] transition">
                                    إزالة
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        @include('panel_v1.instructor.components.empty-state', [
            'title' => 'لا توجد دورات في هذه الحزمة بعد',
        ])
    @endif
</div>
@endsection
