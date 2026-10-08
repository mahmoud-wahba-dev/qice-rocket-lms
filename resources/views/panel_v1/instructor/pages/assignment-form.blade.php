@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $assignment = $assignment ?? null;
    $chapters = $chapters ?? [];
    $existingAttachment = $existingAttachment ?? [];
    $inputClass = 'input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary';
    $labelClass = 'font-semibold text-14px text-primary mb-2 block';
@endphp

<div class="space-y-6 pb-10 max-w-3xl">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'تعديل التكليف',
        'subtitle' => 'عدّل العنوان والوصف والملف المرفق ثم احفظ',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.assignments') }}"
                class="inline-flex items-center gap-2 rounded-12px border border-d9 px-5 h-12 font-semibold text-15px text-primary bg-white hover:bg-fa transition">
                رجوع
            </a>
        @endslot
    @endcomponent

    <form method="POST"
        action="{{ route('panel.v1.instructor.assignments.update', ['id' => $assignment->id]) }}"
        enctype="multipart/form-data"
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

        <div class="rounded-12px bg-[#F8FAFC] border border-d9 px-4 py-3">
            <p class="font-medium text-13px text-gray mb-1">الدورة</p>
            <p class="font-semibold text-15px text-primary">{{ $assignment->webinar->title ?? '—' }}</p>
        </div>

        <div>
            <label for="edit-assign-chapter" class="{{ $labelClass }}">الوحدة</label>
            <select id="edit-assign-chapter" name="chapter_id" class="select select-bordered {{ $inputClass }}">
                <option value="">تُنشأ وحدة تلقائياً إن لزم</option>
                @foreach ($chapters as $chapter)
                    <option value="{{ $chapter['id'] }}" @selected(old('chapter_id', $assignment->chapter_id) == $chapter['id'])>
                        {{ $chapter['title'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="edit-assign-title" class="{{ $labelClass }}">عنوان التكليف *</label>
            <input id="edit-assign-title" name="title" type="text" required maxlength="255"
                value="{{ old('title', optional($assignment->translate('ar'))->title ?: ($assignment->title ?? '')) }}" class="{{ $inputClass }}">
        </div>

        <div>
            <label for="edit-assign-description" class="{{ $labelClass }}">الوصف / المطلوب *</label>
            <textarea id="edit-assign-description" name="description" required rows="4"
                class="textarea textarea-bordered w-full rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary min-h-28">{{ old('description', optional($assignment->translate('ar'))->description ?: ($assignment->description ?? '')) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">الدرجة العظمى *</label>
                <input name="grade" type="number" required min="1" max="1000"
                    value="{{ old('grade', $assignment->grade) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">درجة النجاح *</label>
                <input name="pass_grade" type="number" required min="0" max="1000"
                    value="{{ old('pass_grade', $assignment->pass_grade) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">الموعد (أيام من الشراء)</label>
                <input name="deadline" type="number" min="1" max="365"
                    value="{{ old('deadline', $assignment->deadline) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">عدد المحاولات</label>
                <input name="attempts" type="number" min="1" max="50"
                    value="{{ old('attempts', $assignment->attempts) }}" class="{{ $inputClass }}">
            </div>
        </div>

        <div>
            <label class="{{ $labelClass }}">حالة النشر</label>
            <select name="status" class="select select-bordered {{ $inputClass }}">
                <option value="active" @selected(old('status', $assignment->status) === 'active')>نشط</option>
                <option value="inactive" @selected(old('status', $assignment->status) === 'inactive')>معطل</option>
            </select>
        </div>

        <div>
            <label class="{{ $labelClass }}">ملف مرفق للتكليف</label>
            @include('panel_v1.components.file-upload', [
                'name' => 'attachment',
                'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.zip,image/*',
                'label' => 'رفع ملف جديد (يستبدل الحالي)',
                'hint' => 'PDF أو Word أو صورة',
                'required' => false,
                'existing' => $existingAttachment,
                'existingLabel' => 'الملف الحالي للطالب',
                'compact' => true,
            ])
            @if (!empty($existingAttachment))
                <label class="mt-3 inline-flex items-center gap-2 font-medium text-14px text-gray cursor-pointer">
                    <input type="checkbox" name="remove_attachment" value="1" class="checkbox checkbox-sm checkbox-primary"
                        @checked(old('remove_attachment'))>
                    حذف الملف المرفق الحالي
                </label>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
            <a href="{{ route('panel.v1.instructor.assignments') }}"
                class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-primary">
                إلغاء
            </a>
            <button type="submit" class="btn btn-primary rounded-12px h-12 px-8 font-bold text-15px">
                حفظ التعديلات
            </button>
        </div>
    </form>
</div>
@endsection
