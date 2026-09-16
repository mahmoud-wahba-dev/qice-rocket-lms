@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($product) && $product; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل منتج' : 'إنشاء منتج','subtitle'=>'منطق Admin\Store\ProductsController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">المنشئ *</label><input type="number" name="creator_id" value="{{ old('creator_id', $product->creator_id ?? auth()->id()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="physical" @selected(old('type', $product->type ?? '')=='physical')>مادي</option>
                        <option value="virtual" @selected(old('type', $product->type ?? '')=='virtual')>افتراضي</option>
                    </select>
                </div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $product->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الرابط</label><input type="text" name="slug" value="{{ old('slug', $product->slug ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" dir="ltr"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف SEO *</label><input type="text" name="seo_description" value="{{ old('seo_description', $product->seo_description ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الملخص *</label><textarea name="summary" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('summary', $product->summary ?? '') }}</textarea></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف *</label><textarea name="description" rows="4" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description', $product->description ?? '') }}</textarea></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">النقاط</label><input type="number" name="point" value="{{ old('point', $product->point ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الضريبة</label><input type="number" name="tax" value="{{ old('tax', $product->tax ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'products']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
