@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'إنشاء قسيمة جديدة','subtitle'=>'منطق Admin\DiscountController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الكود *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: QIEC20" dir="ltr">
                    @error('code')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: خصم التأسيس">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النسبة %</label>
                    <input type="number" name="percent" value="{{ old('percent') }}" min="0" max="100" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">العدد</label>
                    <input type="number" name="count" value="{{ old('count', 1) }}" min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">نوع الخصم *</label>
                    <select name="discount_type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="percentage" selected>نسبة مئوية</option>
                        <option value="fixed_amount">مبلغ ثابت</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المصدر *</label>
                    <select name="source" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="all" selected>الكل</option>
                        <option value="course">دورة</option>
                        <option value="bundle">حزمة</option>
                        <option value="meeting">اجتماع</option>
                        <option value="product">منتج</option>
                        <option value="event">فعالية</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء القسيمة</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'discounts']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
