@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> !empty($template) ? 'تعديل قالب: '.($template->title ?? '#'.$template->id) : 'إنشاء قالب شهادة','subtitle'=>'منطق Admin\CertificateController حرفياً — CertificateTemplate + Translation'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px"><option value="ar" @selected(old('locale','ar')=='ar')>العربية</option><option value="en" @selected(old('locale')=='en')>English</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">النوع *</label><select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px"><option value="course" @selected(old('type', $template->type ?? 'course')=='course')>دورة</option><option value="quiz" @selected(old('type', $template->type ?? '')=='quiz')>اختبار</option><option value="bundle" @selected(old('type', $template->type ?? '')=='bundle')>حزمة</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الحالة</label><select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px"><option value="draft" @selected(old('status', $template->status ?? 'draft')=='draft')>مسودة</option><option value="publish" @selected(old('status', $template->status ?? '')=='publish')>منشور</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">صورة الخلفية *</label><input type="text" name="image" value="{{ old('image', $template->image ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/assets/...">@error('image')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $template->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">@error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">محتوى القالب (body) — يدعم [student] [course] [date] [grade]</label><textarea name="template_contents" rows="4" class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('template_contents', optional(optional($template)->translate(app()->getLocale()))->body ?? '') }}</textarea></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ !empty($template) ? 'حفظ التعديلات' : 'إنشاء القالب' }}</button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'certificates']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
