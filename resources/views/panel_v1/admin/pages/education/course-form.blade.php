@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => $course ? 'تعديل دورة' : 'إنشاء دورة جديدة',
        'subtitle' => $course ? $course->title : 'أدخل بيانات الدورة — منطق حرفي من Admin\WebinarController',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">عنوان الدورة *</label>
                    <input type="text" name="title" value="{{ old('title', $course->title ?? '') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: دورة تطوير الويب">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المدرب *</label>
                    <select name="teacher_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر مدرب</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}" @selected(old('teacher_id', $course->teacher_id ?? '') == $t->id)>{{ $t->full_name }} (#{{ $t->id }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">التصنيف *</label>
                    <select name="category_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر تصنيف</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat['id'] }}" @selected(old('category_id', $course->category_id ?? '') == $cat['id'])>{{ $cat['title'] }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="course" @selected(old('type', $course->type ?? 'course')=='course')>دورة مسجلة (course)</option>
                        <option value="webinar" @selected(old('type', $course->type ?? '')=='webinar')>مباشرة (webinar)</option>
                        <option value="text_lesson" @selected(old('type', $course->type ?? '')=='text_lesson')>نصية (text_lesson)</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة *</label>
                    <select name="status" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="pending" @selected(old('status', $course->status ?? 'pending')=='pending')>بانتظار المراجعة</option>
                        <option value="active" @selected(old('status', $course->status ?? '')=='active')>نشط</option>
                        <option value="is_draft" @selected(old('status', $course->status ?? '')=='is_draft')>مسودة</option>
                        <option value="inactive" @selected(old('status', $course->status ?? '')=='inactive')>مرفوض</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">السعر (ر.س)</label>
                    <input type="number" name="price" value="{{ old('price', isset($course->price) ? (int)$course->price : '') }}" min="0"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="0 = مجانية">
                    @error('price')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المدة (دقيقة)</label>
                    <input type="number" name="duration" value="{{ old('duration', $course->duration ?? 60) }}" min="1"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">السعة (مقعد)</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $course->capacity ?? '') }}" min="1"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="فارغ = غير محدود">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">اللغة</label>
                    <select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="ar" @selected(old('locale','ar')=='ar')>العربية</option>
                        <option value="en" @selected(old('locale')=='en')>English</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">ملخص قصير</label>
                    <textarea name="summary" rows="2" class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3" placeholder="ملخص يظهر في البطاقة">{{ old('summary', $course->summary ?? '') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الوصف التفصيلي</label>
                    <textarea name="description" rows="4" class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3" placeholder="وصف كامل للدورة">{{ old('description', $course->description ?? '') }}</textarea>
                </div>

                <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="support" value="on" @checked(old('support', $course->support ?? true)) class="checkbox checkbox-sm"> دعم</label>
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="certificate" value="on" @checked(old('certificate', $course->certificate ?? true)) class="checkbox checkbox-sm"> شهادة</label>
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="downloadable" value="on" @checked(old('downloadable', $course->downloadable ?? false)) class="checkbox checkbox-sm"> قابل للتحميل</label>
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="private" value="on" @checked(old('private', $course->private ?? false)) class="checkbox checkbox-sm"> خاص</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                    {{ $course ? 'حفظ التعديلات' : 'إنشاء الدورة' }}
                </button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'courses']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>

        <p class="font-medium text-12px text-gray mt-6 leading-relaxed">
            * تنفيذ حرفي مبسط من <code>Admin\WebinarController::store:350</code> — ينشئ <code>webinars</code> + <code>webinar_translations</code> مع <code>slug</code> و <code>handlePrice</code>، قابل للتوسعة (صورة/وصف/سعة) بدون تغيير ثيم.
        </p>
    </div>
</div>
@endsection
