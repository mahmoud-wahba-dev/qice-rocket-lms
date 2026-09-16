@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(new \Illuminate\Support\MessageBag());
    $bundleCards = $bundleCards ?? [];
    $stats = $bundleStats ?? [
        'total' => 0,
        'hours_label' => '00:00',
        'sales_count' => 0,
        'sales_amount' => 0,
    ];
    $teacherCourses = $teacherCourses ?? [];
    $input = 'input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-black focus:outline-none focus:border-primary';
    $statCard = 'rounded-14px border border-d9 bg-white p-4 sm:p-5';
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'حزم الدورات والباقات',
        'subtitle' => 'إدارة باقاتك، متابعة المبيعات، وإنشاء حزم جديدة من دوراتك.',
    ])
        @slot('actions')
            <a href="#create-bundle"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                إنشاء حزمة
            </a>
        @endslot
    @endcomponent

    {{-- Rocket top stats → panel_v1 tokens --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $statCard }}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <p class="font-medium text-14px text-gray pt-1">الحزم</p>
                <span class="size-12 rounded-12px bg-primary/10 center shrink-0">
                    <span class="icon-[tabler--package] size-6 text-primary"></span>
                </span>
            </div>
            <p class="font-bold text-24px text-primary leading-none">{{ (int) ($stats['total'] ?? 0) }}</p>
        </div>
        <div class="{{ $statCard }}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <p class="font-medium text-14px text-gray pt-1">الساعات</p>
                <span class="size-12 rounded-12px bg-[#ECFDF5] center shrink-0">
                    <span class="icon-[tabler--clock] size-6 text-[#059669]"></span>
                </span>
            </div>
            <p class="font-bold text-24px text-primary leading-none">{{ $stats['hours_label'] ?? '00:00' }}</p>
        </div>
        <div class="{{ $statCard }}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <p class="font-medium text-14px text-gray pt-1">مبيعات الحزم</p>
                <span class="size-12 rounded-12px bg-[#FFF7ED] center shrink-0">
                    <span class="icon-[tabler--shopping-bag] size-6 text-[#EA580C]"></span>
                </span>
            </div>
            <p class="font-bold text-24px text-primary leading-none">{{ (int) ($stats['sales_count'] ?? 0) }}</p>
        </div>
        <div class="{{ $statCard }}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <p class="font-medium text-14px text-gray pt-1">إيرادات الحزم</p>
                <span class="size-12 rounded-12px bg-[#F7F0E6] center shrink-0">
                    <span class="icon-[tabler--currency-riyal] size-6 text-[#8B6914]"></span>
                </span>
            </div>
            <p class="font-bold text-24px text-primary leading-none">{{ handlePrice($stats['sales_amount'] ?? 0) }}</p>
        </div>
    </div>

    @if (!empty($bundleCards))
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
            @foreach ($bundleCards as $index => $bundle)
                @include('panel_v1.instructor.components.bundle-card', [
                    'bundle' => $bundle,
                    'index' => $index,
                ])
            @endforeach
        </div>
    @else
        @include('panel_v1.instructor.components.empty-state', [
            'title' => 'لا توجد حزم بعد',
            'subtitle' => 'أنشئ أول باقة لتجميع دوراتك وبيعها بسعر موحّد.',
        ])
    @endif

    {{-- Create form (v1) — kept below list like Rocket "create" CTA destination --}}
    <section id="create-bundle" class="rounded-14px border border-d9 bg-white px-5 sm:px-7 py-6 sm:py-8">
        <div class="flex items-center gap-3 mb-5">
            <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--plus] size-5 text-primary"></span>
            </span>
            <div class="text-start min-w-0">
                <h2 class="font-bold text-18px sm:text-20px text-primary">إنشاء حزمة جديدة</h2>
                <p class="font-medium text-13px text-gray">اختر الدورات وحدد السعر — أو استخدم المحرر الكامل من القائمة القديمة للتفاصيل المتقدمة</p>
            </div>
        </div>

        <form method="POST" action="{{ route('panel.v1.instructor.bundles.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">عنوان الحزمة <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="{{ $input }}"
                        placeholder="مثال: باقة التأسيس الشاملة">
                    @error('title')
                        <p class="mt-1 font-medium text-13px text-[#B91C1C]">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">السعر (فارغ = مجانية)</label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0" class="{{ $input }}" placeholder="مثال: 499">
                </div>
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">وصف مختصر</label>
                <textarea name="summary" rows="2" class="{{ $input }} min-h-[72px] h-auto py-3"
                    placeholder="لماذا يشتري الطالب هذه الباقة؟">{{ old('summary') }}</textarea>
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">الدورات ضمن الحزمة</label>
                @if (!empty($teacherCourses))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto border border-d9 rounded-12px p-3 bg-[#FAFAF4]">
                        @foreach ($teacherCourses as $course)
                            <label class="flex items-start gap-2 cursor-pointer rounded-8px px-2 py-2 hover:bg-white transition">
                                <input type="checkbox" name="webinar_ids[]" value="{{ $course['id'] }}"
                                    class="checkbox checkbox-primary mt-0.5 shrink-0"
                                    {{ in_array((string) $course['id'], array_map('strval', (array) old('webinar_ids', [])), true) ? 'checked' : '' }}>
                                <span class="font-medium text-14px text-primary leading-snug">{{ $course['title'] }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="font-medium text-14px text-gray">لا توجد دورات منشورة بعد — أنشئ دورة أولًا ثم أضفها للحزمة.</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" name="publish" value="0"
                    class="inline-flex items-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-15px text-primary hover:bg-[#FAFAF4] transition">
                    حفظ كمسودة
                </button>
                <button type="submit" name="publish" value="1"
                    class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-semibold text-15px hover:opacity-90 transition">
                    <span class="icon-[tabler--send] size-4"></span>
                    إرسال للمراجعة
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
