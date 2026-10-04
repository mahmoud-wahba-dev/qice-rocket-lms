@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $students = $students ?? [];
    $courses = $courses ?? [];
    $defaults = $defaults ?? [];
    $listUrl = route('panel.v1.admin.education.section', ['section' => 'certificates']);
    $input = 'input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none';
    $select = 'select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white focus:border-primary focus:outline-none';
    $label = 'font-semibold text-14px text-primary mb-2 block';
@endphp

<div class="space-y-6 pb-10">
    @component('panel_v1.admin.components.page-header', [
        'title' => 'إنشاء شهادة جديدة',
        'subtitle' => 'بيانات الشهادة تُطبع مباشرة على قالب QIEC الثنائي اللغة',
    ])
        @slot('actions')
            <a href="{{ $listUrl }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                العودة للقائمة
            </a>
        @endslot
    @endcomponent

    @if ($errors->any())
        <div class="rounded-12px bg-[#FEF2F2] border border-[#FECACA] px-4 py-3">
            <ul class="list-disc list-inside space-y-1 font-medium text-14px text-[#DC2626]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('panel.v1.admin.education.certificates.store') }}" class="space-y-5">
        @csrf

        <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8 space-y-5">
            <h2 class="font-bold text-18px text-primary">ربط الشهادة</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">المتدرب *</label>
                    <select name="student_id" required class="{{ $select }}" data-student-select>
                        <option value="">— اختر متدربًا —</option>
                        @foreach ($students as $student)
                            <option value="{{ $student['id'] }}"
                                data-name="{{ $student['name'] }}"
                                @selected((string) old('student_id') === (string) $student['id'])>
                                {{ $student['name'] }} @if(!empty($student['email'])) ({{ $student['email'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">الدورة *</label>
                    <select name="webinar_id" required class="{{ $select }}" data-course-select>
                        <option value="">— اختر دورة —</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course['id'] }}"
                                data-title-ar="{{ $course['title_ar'] }}"
                                data-title-en="{{ $course['title_en'] }}"
                                data-hours="{{ $course['hours'] }}"
                                @selected((string) old('webinar_id') === (string) $course['id'])>
                                {{ $course['title_ar'] }} (#{{ $course['id'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8 space-y-5">
            <h2 class="font-bold text-18px text-primary">بيانات المطبوعة على الشهادة</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">اسم المتدرب (عربي) *</label>
                    <input type="text" name="trainee_name_ar" value="{{ old('trainee_name_ar') }}" required class="{{ $input }}" placeholder="الاسم الكامل للمتدرب/ة">
                </div>
                <div>
                    <label class="{{ $label }}">Trainee name (English)</label>
                    <input type="text" name="trainee_name_en" value="{{ old('trainee_name_en') }}" class="{{ $input }}" dir="ltr" placeholder="Full trainee name">
                </div>
                <div>
                    <label class="{{ $label }}">اسم الدورة (عربي) *</label>
                    <input type="text" name="course_title_ar" value="{{ old('course_title_ar') }}" required class="{{ $input }}" data-course-title-ar>
                </div>
                <div>
                    <label class="{{ $label }}">Course title (English)</label>
                    <input type="text" name="course_title_en" value="{{ old('course_title_en') }}" class="{{ $input }}" dir="ltr" data-course-title-en>
                </div>
                <div>
                    <label class="{{ $label }}">عدد الساعات التدريبية *</label>
                    <input type="number" min="1" max="999" name="hours" value="{{ old('hours', $defaults['hours'] ?? 8) }}" required class="{{ $input }}" data-course-hours>
                </div>
                <div>
                    <label class="{{ $label }}">رقم الاعتماد *</label>
                    <input type="text" name="accreditation_number" value="{{ old('accreditation_number', $defaults['accreditation_number'] ?? 'QIEC-ACC-001') }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">تاريخ بداية الدورة *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $defaults['start_date'] ?? date('Y-m-d')) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">تاريخ نهاية الدورة *</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $defaults['end_date'] ?? date('Y-m-d')) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">تاريخ الإصدار *</label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', $defaults['issue_date'] ?? date('Y-m-d')) }}" required class="{{ $input }}">
                </div>
            </div>
        </div>

        <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8 space-y-5">
            <h2 class="font-bold text-18px text-primary">التوقيعات</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Training & Accreditation Officer *</label>
                    <input type="text" name="officer_name" value="{{ old('officer_name', $defaults['officer_name'] ?? '') }}" required class="{{ $input }}" placeholder="اسم مسؤول التدريب والاعتماد">
                    <p class="mt-1 font-medium text-12px text-gray">يظهر أسفل اليسار على الشهادة</p>
                </div>
                <div>
                    <label class="{{ $label }}">مدير المركز *</label>
                    <input type="text" name="director_name" value="{{ old('director_name', $defaults['director_name'] ?? '') }}" required class="{{ $input }}" placeholder="اسم مدير المركز">
                    <p class="mt-1 font-medium text-12px text-gray">يظهر أسفل اليمين على الشهادة</p>
                </div>
            </div>
            <label class="flex items-center gap-2 font-medium text-13px text-primary">
                <input type="checkbox" name="save_as_defaults" value="1" class="checkbox checkbox-sm" @checked(old('save_as_defaults', true))>
                حفظ أسماء التوقيع ورقم الاعتماد كافتراضي للشهادات التالية
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                إنشاء الشهادة
            </button>
            <a href="{{ $listUrl }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                إلغاء
            </a>
        </div>
    </form>
</div>

<script>
(() => {
    const courseSelect = document.querySelector('[data-course-select]');
    const studentSelect = document.querySelector('[data-student-select]');
    const titleAr = document.querySelector('[data-course-title-ar]');
    const titleEn = document.querySelector('[data-course-title-en]');
    const hours = document.querySelector('[data-course-hours]');
    const nameAr = document.querySelector('input[name="trainee_name_ar"]');
    const nameEn = document.querySelector('input[name="trainee_name_en"]');

    if (courseSelect) {
        courseSelect.addEventListener('change', () => {
            const opt = courseSelect.options[courseSelect.selectedIndex];
            if (!opt || !opt.value) return;
            if (titleAr) titleAr.value = opt.getAttribute('data-title-ar') || '';
            if (titleEn) titleEn.value = opt.getAttribute('data-title-en') || '';
            if (hours && opt.getAttribute('data-hours')) hours.value = opt.getAttribute('data-hours');
        });
    }
    if (studentSelect) {
        studentSelect.addEventListener('change', () => {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            if (!opt || !opt.value) return;
            const n = opt.getAttribute('data-name') || '';
            if (nameAr && !nameAr.value) nameAr.value = n;
            if (nameEn && !nameEn.value) nameEn.value = n;
        });
    }
})();
</script>
@endsection
