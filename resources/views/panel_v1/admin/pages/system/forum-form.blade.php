@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($forum) && $forum; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل منتدى' : 'منتدى جديد','subtitle'=>'منطق Admin\ForumController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $forum->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف *</label><textarea name="description" rows="3" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description', $forum->description ?? '') }}</textarea></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيقونة *</label><input type="text" name="icon" value="{{ old('icon', $forum->icon ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الغلاف *</label><input type="text" name="cover" value="{{ old('cover', $forum->cover ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة</label>
                    <select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="active" @selected(old('status', $forum->status ?? 'active')=='active')>نشط</option>
                        <option value="disabled" @selected(old('status', $forum->status ?? 'active')=='disabled')>معطل</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6"><label class="flex items-center gap-2"><input type="checkbox" name="close" value="1" @checked($forum->close ?? false) class="checkbox checkbox-sm"> مغلق</label></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.system.section',['section'=>'forums']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
