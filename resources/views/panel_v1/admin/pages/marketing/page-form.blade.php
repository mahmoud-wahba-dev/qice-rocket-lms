@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = !empty($page); $pageTr = $isEdit ? $page->translate(app()->getLocale()) : null; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل صفحة: '.$page->name : 'إنشاء صفحة جديدة','subtitle'=>'منطق Admin\PagesController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="ar"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الاسم البرمجي *</label><input type="text" name="name" value="{{ old('name', $page->name ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الرابط *</label><input type="text" name="link" value="{{ old('link', $page->link ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/about" dir="ltr"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $pageTr->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان الفرعي *</label><input type="text" name="subtitle" value="{{ old('subtitle', $pageTr->subtitle ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيقونة *</label><input type="text" name="icon" value="{{ old('icon', $page->icon ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الغلاف *</label><input type="text" name="cover" value="{{ old('cover', $page->cover ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">أيقونة الهيدر *</label><input type="text" name="header_icon" value="{{ old('header_icon', $page->header_icon ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة</label>
                    <select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="publish" @selected(old('status', $page->status ?? 'draft')=='publish')>نشر</option>
                        <option value="draft" @selected(old('status', $page->status ?? 'draft')=='draft')>مسودة</option>
                    </select>
                </div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف SEO</label><input type="text" name="seo_description" value="{{ old('seo_description', $pageTr->seo_description ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">المحتوى *</label><textarea name="content" rows="6" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('content', $pageTr->content ?? '') }}</textarea></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الصفحة' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'pages']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
