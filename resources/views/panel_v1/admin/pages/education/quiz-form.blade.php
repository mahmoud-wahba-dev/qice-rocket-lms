@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $webinars = $webinars ?? [];
    $formAction = $formAction ?? route('panel.v1.admin.education.quizzes.store');
@endphp

<div class="space-y-6 pb-10 max-w-3xl">
    @component('panel_v1.admin.components.page-header', [
        'title' => 'إضافة اختبار جديد',
        'subtitle' => 'الخطوة 1 من 2: بيانات الاختبار — بعد الحفظ ستنتقل لإضافة الأسئلة بنفس أسلوب لوحة المدرب',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.admin.education.section', ['section' => 'quizzes']) }}"
                class="inline-flex items-center gap-2 rounded-12px border border-d9 px-5 h-12 font-semibold text-15px text-primary bg-white hover:bg-[#FAFAF4] transition">
                إلغاء
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-[#BFDBFE] bg-[#EFF6FF] px-5 py-4 flex items-start gap-3">
        <span class="icon-[tabler--info-circle] size-5 text-[#1D4ED8] shrink-0 mt-0.5"></span>
        <p class="font-medium text-14px text-[#1E3A8A] leading-relaxed">
            بعد إنشاء الاختبار ستفتح صفحة إدارة الأسئلة لإضافة الأسئلة واختيار ○ الإجابة الصحيحة لكل سؤال — نفس دورة المدرب.
        </p>
    </div>

    <form method="POST" action="{{ $formAction }}"
        class="border border-d9 rounded-20px bg-white px-5 sm:px-8 py-8 space-y-6 shadow-sm">
        @csrf

        @if ($errors->any())
            <div class="rounded-12px bg-[#FEF2F2] border border-[#FECACA] px-4 py-3">
                <ul class="list-disc list-inside space-y-1 font-medium text-14px text-[#DC2626]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label class="block font-semibold text-14px text-primary mb-2">اختر الدورة <span class="text-[#E11D48]">*</span></label>
            <select name="webinar_id" required
                class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary bg-white">
                <option value="">— اختر الدورة —</option>
                @foreach ($webinars as $w)
                    <option value="{{ $w['id'] }}" @selected(old('webinar_id') == $w['id'])>
                        {{ $w['title'] }} (#{{ $w['id'] }})
                    </option>
                @endforeach
            </select>
            @error('webinar_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-semibold text-14px text-primary mb-2">عنوان الاختبار <span class="text-[#E11D48]">*</span></label>
            <input type="text" name="title" required maxlength="255"
                value="{{ old('title') }}"
                placeholder="مثال: اختبار الوحدة الأولى"
                class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">درجة النجاح <span class="text-[#E11D48]">*</span></label>
                <input type="number" name="pass_mark" value="{{ old('pass_mark', 50) }}" min="0" required
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">المدة (دقيقة)</label>
                <input type="number" name="time" value="{{ old('time') }}" min="0"
                    placeholder="فارغ = مفتوح"
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">المحاولات</label>
                <input type="number" name="attempt" value="{{ old('attempt') }}" min="1"
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-14px text-primary mb-2">الحالة <span class="text-[#E11D48]">*</span></label>
            <select name="status" required
                class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary bg-white">
                <option value="active" @selected(old('status', 'active') === 'active')>نشط</option>
                <option value="inactive" @selected(old('status') === 'inactive')>معطل</option>
            </select>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-d9">
            <p class="font-medium text-13px text-gray">الخطوة التالية: إضافة أسئلة الاختبار</p>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('panel.v1.admin.education.section', ['section' => 'quizzes']) }}"
                    class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                    إلغاء
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                    إنشاء والانتقال للأسئلة
                    <span class="icon-[tabler--arrow-narrow-left] size-4"></span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
