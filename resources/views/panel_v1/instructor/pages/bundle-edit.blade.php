@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(new \Illuminate\Support\MessageBag());
    $b = $bundleEdit ?? [];
    $teacherCourses = $teacherCourses ?? [];
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px text-black focus:outline-none focus:border-primary';
    $textarea = 'textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px text-black focus:outline-none focus:border-primary min-h-28 resize-y';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'تعديل الحزمة',
        'subtitle' => $b['title'] ?? '',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.bundles') }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px border border-d9 font-semibold text-15px text-primary hover:bg-fa transition">العودة</a>
            <a href="{{ $b['courses_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px border border-d9 font-semibold text-15px text-primary hover:bg-fa transition">دورات الحزمة</a>
            <a href="{{ $b['preview_url'] ?? '#' }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px bg-color2 font-semibold text-15px text-white hover:opacity-95 transition">عرض عام</a>
        @endslot
    @endcomponent

    <form method="POST" action="{{ route('panel.v1.instructor.bundles.update', ['id' => $b['id'] ?? 0]) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <section class="{{ $card }}">
            <h2 class="font-bold text-18px text-primary mb-5">المعلومات الأساسية</h2>
            <div class="space-y-4">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">عنوان الحزمة <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required value="{{ old('title', $b['title'] ?? '') }}" class="{{ $input }}">
                    @error('title')<p class="mt-1 font-medium text-13px text-[#B91C1C]">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">الوصف المختصر / SEO</label>
                    <input type="text" name="seo_description" maxlength="160" value="{{ old('seo_description', $b['seo_description'] ?? '') }}" class="{{ $input }}" placeholder="50–160 حرفًا مفضلة">
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">الملخص</label>
                    <textarea name="summary" rows="3" class="{{ $textarea }}">{{ old('summary', $b['summary'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">الوصف التفصيلي</label>
                    <textarea name="description" rows="8" class="{{ $textarea }} min-h-48">{{ old('description', $b['description'] ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="font-bold text-18px text-primary mb-5">الصور والفيديو</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                @foreach ([
                    ['thumbnail', 'الصورة المصغرة', $b['thumbnail'] ?? null],
                    ['image_cover', 'غلاف الحزمة', $b['image_cover'] ?? null],
                ] as [$name, $label, $current])
                    <label class="flex flex-col items-center justify-center gap-3 min-h-40 rounded-14px border border-dashed border-d9 bg-[#F7F0E6]/40 px-4 py-6 cursor-pointer hover:border-primary/40 transition">
                        @if (!empty($current))
                            <img src="{{ $current }}" alt="" class="h-20 w-auto rounded-8px object-cover mb-1">
                            <span class="font-medium text-12px text-gray">ملف محفوظ — اختر للاستبدال</span>
                        @else
                            <span class="icon-[tabler--cloud-upload] size-8 text-primary"></span>
                        @endif
                        <span class="font-semibold text-15px text-primary">{{ $label }}</span>
                        <input type="file" name="{{ $name }}" accept="image/*" class="hidden">
                    </label>
                @endforeach
            </div>
            <div>
                <label class="block font-semibold text-14px text-primary mb-2">رابط فيديو ترويجي (اختياري)</label>
                <input type="url" name="video_demo" value="{{ old('video_demo', $b['video_demo'] ?? '') }}" class="{{ $input }}" placeholder="https://www.youtube.com/watch?v=...">
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="font-bold text-18px text-primary mb-5">التسعير والحالة</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">السعر</label>
                    <input type="number" name="price" min="0" value="{{ old('price', $b['price'] ?? '') }}" class="{{ $input }}" placeholder="اتركه فارغًا للمجانية">
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">الحالة</label>
                    <select name="status" class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px">
                        @foreach (['is_draft' => 'مسودة', 'pending' => 'قيد المراجعة', 'active' => 'منشورة', 'inactive' => 'معطّلة'] as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $b['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="font-bold text-18px text-primary mb-2">الدورات ضمن الحزمة</h2>
            <p class="font-medium text-13px text-gray mb-4">حدّد الدورات التي تظهر داخل هذه الباقة</p>
            @if (!empty($teacherCourses))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto border border-d9 rounded-12px p-3 bg-[#FAFAF4]">
                    @foreach ($teacherCourses as $course)
                        <label class="flex items-start gap-2 cursor-pointer rounded-8px px-2 py-2 hover:bg-white transition">
                            @php
                                $oldIds = old('webinar_ids');
                                $isChecked = is_array($oldIds)
                                    ? in_array((string) $course['id'], array_map('strval', $oldIds), true)
                                    : !empty($course['selected']);
                            @endphp
                            <input type="checkbox" name="webinar_ids[]" value="{{ $course['id'] }}"
                                class="checkbox checkbox-primary mt-0.5 shrink-0"
                                {{ $isChecked ? 'checked' : '' }}>
                            <span class="font-medium text-14px text-primary leading-snug">{{ $course['title'] }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="font-medium text-14px text-gray">لا توجد دورات متاحة.</p>
            @endif
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 h-12 px-6 rounded-12px bg-primary text-white font-bold text-15px hover:opacity-90 transition">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                حفظ التعديلات
            </button>
            <a href="{{ route('panel.v1.instructor.bundles') }}" class="font-semibold text-15px text-gray hover:text-primary transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection
