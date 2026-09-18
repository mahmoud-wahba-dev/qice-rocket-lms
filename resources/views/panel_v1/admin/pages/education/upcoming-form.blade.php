@extends('panel_v1.admin.layouts.app')

@section('content')
@php $isEdit = isset($upcomingCourse) && $upcomingCourse; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => $isEdit ? 'تعديل دورة قادمة' : 'دورة قادمة جديدة',
        'subtitle' => $isEdit ? ($upcomingCourse->title ?? 'تعديل') : 'أدخل بيانات الدورة القادمة — منطق Admin\UpcomingCoursesController حرفياً',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">عنوان الدورة *</label>
                    <input type="text" name="title" value="{{ old('title', $upcomingCourse->title ?? '') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: دورة القيادة القادمة">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المدرب *</label>
                    <select name="teacher_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر مدرب</option>
                        @foreach ($teachers ?? [] as $t)
                            <option value="{{ is_array($t) ? $t['id'] : $t->id }}" @selected(old('teacher_id', $upcomingCourse->teacher_id ?? '') == (is_array($t) ? $t['id'] : $t->id))>{{ is_array($t) ? ($t['title'] ?? 'مدرب') : $t->full_name }} (#{{ is_array($t) ? $t['id'] : $t->id }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">التصنيف *</label>
                    <select name="category_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر تصنيف</option>
                        @foreach ($categories ?? [] as $cat)
                            @php $cid = is_array($cat) ? $cat['id'] : $cat->id; @endphp
                            @php $ctitle = is_array($cat) ? $cat['title'] : $cat->title; @endphp
                            <option value="{{ $cid }}" @selected(old('category_id', $upcomingCourse->category_id ?? '') == $cid)>{{ $ctitle }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="course" @selected(old('type', $upcomingCourse->type ?? 'course')=='course')>دورة مسجلة (course)</option>
                        <option value="webinar" @selected(old('type', $upcomingCourse->type ?? '')=='webinar')>مباشرة (webinar)</option>
                        <option value="text_lesson" @selected(old('type', $upcomingCourse->type ?? '')=='text_lesson')>نصية (text_lesson)</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">السعر (ر.س)</label>
                    <input type="number" name="price" value="{{ old('price', isset($upcomingCourse->price) ? (int)$upcomingCourse->price : '') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="فارغ = مجانية">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">تاريخ النشر *</label>
                    <input type="datetime-local" name="publish_date" value="{{ old('publish_date', isset($upcomingCourse->publish_date) ? date('Y-m-d\TH:i', (int)$upcomingCourse->publish_date) : '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                    @error('publish_date')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المنطقة الزمنية *</label>
                    <select name="timezone" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        @php $tz = old('timezone', $upcomingCourse->timezone ?? getTimezone() ?? 'Asia/Riyadh'); @endphp
                        <option value="Asia/Riyadh" @selected($tz=='Asia/Riyadh')>Asia/Riyadh</option>
                        <option value="UTC" @selected($tz=='UTC')>UTC</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">صورة مصغرة *</label>
                    <input type="text" name="thumbnail" value="{{ old('thumbnail', $upcomingCourse->thumbnail ?? '/assets/default/img/course_default.jpg') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/assets/default/img/course_default.jpg">
                    @error('thumbnail')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">غلاف الصورة *</label>
                    <input type="text" name="image_cover" value="{{ old('image_cover', $upcomingCourse->image_cover ?? '/assets/default/img/course_default.jpg') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                    @error('image_cover')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">اللغة</label>
                    <select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="ar" @selected(old('locale','ar')=='ar')>العربية</option>
                        <option value="en" @selected(old('locale')=='en')>English</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">وصف السيو</label>
                    <textarea name="seo_description" rows="2" class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3" placeholder="وصف محركات البحث">{{ old('seo_description', $upcomingCourse->seo_description ?? '') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الوصف التفصيلي *</label>
                    <textarea name="description" rows="4" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3" placeholder="وصف كامل للدورة القادمة">{{ old('description', $upcomingCourse->description ?? '') }}</textarea>
                    @error('description')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                    {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الدورة القادمة' }}
                </button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'upcoming']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
