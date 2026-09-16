@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $p = $preview ?? [];
    $courses = $p['courses'] ?? [];
    $tab = request('tab', 'about');
@endphp

<div class="space-y-6 pb-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('panel.v1.instructor.bundles') }}"
            class="inline-flex items-center gap-2 font-semibold text-15px text-primary hover:opacity-80 transition">
            <span class="icon-[tabler--chevron-right] size-5"></span>
            العودة للحزم
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $p['edit_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 font-semibold text-14px text-primary hover:bg-fa transition">تعديل</a>
            <a href="{{ $p['courses_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 font-semibold text-14px text-primary hover:bg-fa transition">دورات الحزمة</a>
            @if (!empty($p['public_url']))
                <a href="{{ $p['public_url'] }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 h-10 px-4 rounded-12px bg-color2 font-semibold text-14px text-white hover:opacity-95 transition">
                    الصفحة العامة
                    <span class="icon-[tabler--external-link] size-4"></span>
                </a>
            @endif
        </div>
    </div>

    {{-- Hero (Rocket show clone → v1 tokens) --}}
    <section class="relative overflow-hidden rounded-20px min-h-[280px] sm:min-h-[340px] bg-primary text-white">
        @if (!empty($p['cover']))
            <img src="{{ $p['cover'] }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/80 to-primary/40"></div>
        <div class="relative z-10 flex flex-col justify-end h-full min-h-[280px] sm:min-h-[340px] p-5 sm:p-8 text-start">
            <p class="font-medium text-13px text-white/70 mb-2">
                الحزم
                @if (!empty($p['category']))
                    <span class="mx-1">/</span>{{ $p['category'] }}
                @endif
            </p>
            <h1 class="font-bold text-28px sm:text-36px text-white mb-2 leading-snug">{{ $p['title'] ?? '' }}</h1>
            @if (!empty($p['summary']))
                <p class="font-medium text-14px sm:text-15px text-white/80 max-w-3xl mb-4 leading-relaxed">{{ $p['summary'] }}</p>
            @endif
            <div class="flex flex-wrap items-center gap-4 sm:gap-6 mb-5">
                <div class="flex items-center gap-1.5">
                    <span class="icon-[tabler--star-filled] size-4 text-gold"></span>
                    <span class="font-bold text-14px">{{ number_format((float) ($p['rate'] ?? 0), 1) }}</span>
                    <span class="font-medium text-13px text-white/70">({{ (int) ($p['rate_count'] ?? 0) }})</span>
                </div>
                <div class="flex items-center gap-1.5 font-medium text-13px">
                    <span class="icon-[tabler--users] size-4"></span>
                    <span class="font-bold">{{ (int) ($p['students_count'] ?? 0) }}</span>
                    <span class="text-white/70">طلاب</span>
                </div>
                <div class="flex items-center gap-1.5 font-medium text-13px">
                    <span class="icon-[tabler--book] size-4"></span>
                    <span class="font-bold">{{ (int) ($p['courses_count'] ?? 0) }}</span>
                    <span class="text-white/70">دورات</span>
                </div>
                <div class="flex items-center gap-1.5 font-medium text-13px">
                    <span class="icon-[tabler--clock] size-4"></span>
                    <span class="font-bold">{{ $p['duration_label'] ?? '00:00' }}</span>
                    <span class="text-white/70">ساعات</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if (!empty($p['teacher_avatar']))
                    <img src="{{ $p['teacher_avatar'] }}" alt="" class="size-10 rounded-full object-cover border-2 border-white/30">
                @else
                    <span class="size-10 rounded-full bg-white/20 center font-bold">{{ mb_substr($p['teacher_name'] ?? 'م', 0, 1) }}</span>
                @endif
                <div>
                    <p class="font-bold text-14px">{{ $p['teacher_name'] ?? '' }}</p>
                    <p class="font-medium text-12px text-white/70">المدرب</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
        <div class="lg:col-span-8 space-y-5">
            <nav class="flex flex-wrap gap-2 border border-d9 rounded-14px bg-white p-2" role="tablist">
                @foreach ([['about', 'حول الحزمة'], ['courses', 'الدورات']] as [$key, $label])
                    <a href="{{ request()->url() }}?tab={{ $key }}"
                        @class([
                            'inline-flex items-center h-11 px-4 rounded-10px font-semibold text-14px transition',
                            'bg-primary text-white' => $tab === $key,
                            'text-gray hover:bg-fa' => $tab !== $key,
                        ])>{{ $label }}</a>
                @endforeach
            </nav>

            @if ($tab === 'courses')
                <div class="space-y-3">
                    @forelse ($courses as $course)
                        <article class="flex gap-4 items-start border border-d9 rounded-14px bg-white p-4">
                            <div class="size-16 rounded-12px bg-fa overflow-hidden shrink-0">
                                @if (!empty($course['thumbnail']))
                                    <img src="{{ $course['thumbnail'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 text-start">
                                <h3 class="font-bold text-16px text-primary mb-1">{{ $course['title'] }}</h3>
                                @if (!empty($course['summary']))
                                    <p class="font-medium text-13px text-gray line-clamp-2 mb-2">{{ $course['summary'] }}</p>
                                @endif
                                <p class="font-bold text-14px text-primary">{{ $course['price_label'] }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="border border-d9 rounded-14px bg-white p-8 text-center">
                            <p class="font-semibold text-16px text-gray">لا توجد دورات في هذه الحزمة</p>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8 text-start">
                    <h2 class="font-bold text-20px text-primary mb-4">حول الحزمة</h2>
                    @if (!empty($p['description']))
                        <div class="font-medium text-15px text-primary/80 leading-relaxed whitespace-pre-line">{!! nl2br(e($p['description'])) !!}</div>
                    @elseif (!empty($p['summary']))
                        <p class="font-medium text-15px text-primary/80 leading-relaxed">{{ $p['summary'] }}</p>
                    @else
                        <p class="font-medium text-15px text-gray">لا يوجد وصف تفصيلي بعد.</p>
                    @endif
                </div>
            @endif
        </div>

        <aside class="lg:col-span-4">
            <div class="sticky top-24 border border-d9 rounded-14px bg-white overflow-hidden">
                @if (!empty($p['thumbnail']))
                    <img src="{{ $p['thumbnail'] }}" alt="" class="w-full h-44 object-cover">
                @endif
                <div class="p-5 space-y-4">
                    <div>
                        <p class="font-medium text-13px text-gray mb-1">السعر</p>
                        <p class="font-bold text-28px text-primary">{{ $p['price_label'] ?? 'مجانية' }}</p>
                    </div>
                    <ul class="space-y-2.5 font-medium text-14px text-primary">
                        <li class="flex items-center gap-2"><span class="icon-[tabler--book] size-4 text-gray"></span>{{ (int) ($p['courses_count'] ?? 0) }} دورات</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--clock] size-4 text-gray"></span>{{ $p['duration_label'] ?? '00:00' }} ساعات</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--users] size-4 text-gray"></span>{{ (int) ($p['students_count'] ?? 0) }} طلاب</li>
                        <li class="flex items-center gap-2"><span class="icon-[tabler--star] size-4 text-gray"></span>تقييم {{ number_format((float) ($p['rate'] ?? 0), 1) }}</li>
                    </ul>
                    <a href="{{ $p['edit_url'] ?? '#' }}"
                        class="flex items-center justify-center gap-2 h-12 rounded-12px bg-primary text-white font-bold text-15px hover:opacity-90 transition">
                        تعديل الحزمة
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
