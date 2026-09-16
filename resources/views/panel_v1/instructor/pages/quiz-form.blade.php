@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $isEdit = !empty($quiz);
@endphp

<div class="space-y-6 pb-10 max-w-3xl">
    @component('panel_v1.instructor.components.page-header', [
        'title' => $isEdit ? 'تعديل إعدادات الاختبار' : 'إنشاء اختبار جديد',
        'subtitle' => $isEdit
            ? 'عدّل العنوان ودرجة النجاح ثم احفظ — لإضافة الأسئلة ارجع لصفحة إدارة الاختبار'
            : 'الخطوة 1 من 2: بيانات الاختبار — بعد الحفظ ستنتقل لإضافة الأسئلة',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.quizzes') }}"
                class="inline-flex items-center gap-2 rounded-12px border border-d9 px-5 h-12 font-semibold text-15px text-primary bg-white hover:bg-fa transition">
                إلغاء
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-[#BFDBFE] bg-[#EFF6FF] px-5 py-4 flex items-start gap-3">
        <span class="icon-[tabler--info-circle] size-5 text-[#1D4ED8] shrink-0 mt-0.5"></span>
        <p class="font-medium text-14px text-[#1E3A8A] leading-relaxed">
            @if ($isEdit)
                هنا تعدّل إعدادات الاختبار فقط. لإضافة أسئلة أو اختيار الإجابة الصحيحة افتح
                <a href="{{ route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id]) }}" class="font-bold underline">صفحة إدارة الاختبار</a>.
            @else
                بعد إنشاء الاختبار ستفتح صفحة سهلة لإضافة الأسئلة واختيار ○ الإجابة الصحيحة لكل سؤال.
            @endif
        </p>
    </div>

    <form method="POST"
        action="{{ $isEdit ? route('panel.v1.instructor.quizzes.update', ['id' => $quiz->id]) : route('panel.v1.instructor.quizzes.store') }}"
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

        @if (!$isEdit)
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">اختر الدورة <span class="text-[#E11D48]">*</span></label>
                <select name="webinar_id" required
                    class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                    <option value="">— اختر الدورة —</option>
                    @foreach ($webinars ?? [] as $webinar)
                        <option value="{{ $webinar['id'] }}" @selected(old('webinar_id') == $webinar['id'])>
                            {{ $webinar['title'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="rounded-12px bg-[#F8FAFC] border border-d9 px-4 py-3">
                <p class="font-medium text-13px text-gray mb-1">الدورة</p>
                <p class="font-semibold text-15px text-primary">{{ $quiz->webinar->title ?? '—' }}</p>
            </div>
        @endif

        <div>
            <label class="block font-semibold text-14px text-primary mb-2">عنوان الاختبار <span class="text-[#E11D48]">*</span></label>
            <input type="text" name="title" required maxlength="255"
                value="{{ old('title', $quiz->title ?? '') }}"
                placeholder="مثال: اختبار الوحدة الأولى"
                class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">درجة النجاح <span class="text-[#E11D48]">*</span></label>
                <input type="number" name="pass_mark" required min="0"
                    value="{{ old('pass_mark', $quiz->pass_mark ?? 50) }}"
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">المدة (دقيقة)</label>
                <input type="number" name="time" min="0"
                    value="{{ old('time', $quiz->time ?? 20) }}"
                    placeholder="اتركه فارغاً لمفتوح"
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">عدد المحاولات</label>
                <input type="number" name="attempt" min="1"
                    value="{{ old('attempt', $quiz->attempt ?? 3) }}"
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-14px text-primary mb-2">حالة النشر</label>
            <select name="status"
                class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                <option value="active" @selected(old('status', $quiz->status ?? 'active') === 'active')>نشط (يظهر للطالب)</option>
                <option value="inactive" @selected(old('status', $quiz->status ?? '') === 'inactive')>معطل</option>
            </select>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
            @if ($isEdit)
                <a href="{{ route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id]) }}"
                    class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-primary">
                    إدارة الأسئلة
                </a>
            @endif
            <button type="submit" class="btn btn-primary rounded-12px h-12 px-8 font-bold text-15px">
                {{ $isEdit ? 'حفظ الإعدادات' : 'إنشاء والانتقال لإضافة الأسئلة' }}
            </button>
        </div>
    </form>
</div>
@endsection
