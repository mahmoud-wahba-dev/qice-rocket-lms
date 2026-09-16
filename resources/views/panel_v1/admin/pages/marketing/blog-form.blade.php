@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($post) && $post; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل مقال' : 'إنشاء مقال جديد','subtitle'=>'منطق Admin\BlogController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">التصنيف *</label>
                    <select name="category_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        @foreach(($categories ?? []) as $c)<option value="{{ $c->id }}" @selected(old('category_id', $post->category_id ?? '')==$c->id)>{{ $c->title }}</option>@endforeach
                    </select>
                </div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان الفرعي *</label><input type="text" name="subtitle" value="{{ old('subtitle', $post->subtitle ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الصورة *</label><input type="text" name="image" value="{{ old('image', $post->image ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/assets/..."></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">وقت الدراسة (دقيقة)</label><input type="number" name="study_time" value="{{ old('study_time', $post->study_time ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="flex items-center gap-4 pt-6">
                    <label class="flex items-center gap-2"><input type="checkbox" name="enable_comment" value="on" @checked(old('enable_comment', $post->enable_comment ?? true)) class="checkbox checkbox-sm"> تفعيل التعليقات</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="status" value="on" @checked(old('status', ($post->status ?? 'pending')=='publish')) class="checkbox checkbox-sm"> نشر</label>
                </div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف *</label><textarea name="description" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description', $post->description ?? '') }}</textarea></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">المحتوى *</label><textarea name="content" rows="6" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('content', $post->content ?? '') }}</textarea></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'blog']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
