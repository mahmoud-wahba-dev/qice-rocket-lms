@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($subscribe) && $subscribe; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل اشتراك' : 'اشتراك جديد','subtitle'=>'منطق Admin\SubscribesController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيقونة *</label><input type="text" name="icon" value="{{ old('icon', $subscribe->icon ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $subscribe->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان الفرعي *</label><input type="text" name="subtitle" value="{{ old('subtitle', $subscribe->subtitle ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الاستخدامات *</label><input type="number" name="usable_count" value="{{ old('usable_count', $subscribe->usable_count ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيام *</label><input type="number" name="days" value="{{ old('days', $subscribe->days ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">السعر *</label><input type="number" step="0.01" name="price" value="{{ old('price', $subscribe->price ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="flex items-center gap-4 pt-6"><label class="flex items-center gap-2"><input type="checkbox" name="is_popular" value="1" @checked($subscribe->is_popular ?? false) class="checkbox checkbox-sm"> مميز</label><label class="flex items-center gap-2"><input type="checkbox" name="infinite_use" value="1" @checked($subscribe->infinite_use ?? false) class="checkbox checkbox-sm"> استخدام غير محدود</label></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.sales.section',['section'=>'subscriptions']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
