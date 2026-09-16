@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($banner) && $banner; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل بانر' : 'بانر جديد','subtitle'=>'منطق Admin\AdvertisingBannersController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الحالة</label><select name="published" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="1" @selected(old('published', $banner->published ?? 1)==1)>منشور</option><option value="0" @selected(old('published', $banner->published ?? 1)==0)>مسودة</option></select></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الموضع *</label><select name="position" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="home_slider" @selected(old('position', $banner->position ?? '')=='home_slider')>الرئيسية</option><option value="home_products" @selected(old('position', $banner->position ?? '')=='home_products')>منتجات</option><option value="courses" @selected(old('position', $banner->position ?? '')=='courses')>دورات</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الحجم *</label><select name="size" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="419x86" @selected(old('size', $banner->size ?? '')=='419x86')>419x86</option><option value="730x90" @selected(old('size', $banner->size ?? '')=='730x90')>730x90</option></select></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الصورة *</label><input type="text" name="image" value="{{ old('image', $banner->image ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/assets/..."></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الرابط *</label><input type="text" name="link" value="{{ old('link', $banner->link ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="https://..." dir="ltr"></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'banners']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
