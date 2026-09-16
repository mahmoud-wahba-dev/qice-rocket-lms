@extends('panel_v1.admin.layouts.app')
@section('content')
@php $cdTr = !empty($cartDiscount) ? $cartDiscount->translate(app()->getLocale()) : null; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'خصم السلة','subtitle'=>'منطق Admin\CartDiscountController حرفياً — singleton'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ route('panel.v1.admin.marketing.cart-discount.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $cdTr->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان الفرعي *</label><input type="text" name="subtitle" value="{{ old('subtitle', $cdTr->subtitle ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">القسيمة *</label>
                    <select name="discount_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        @foreach(($discounts ?? []) as $d)<option value="{{ $d->id }}" @selected(($cartDiscount->discount_id ?? '')==$d->id)>{{ $d->code }} — {{ $d->title }}</option>@endforeach
                    </select>
                </div>
                <div class="flex items-center gap-4 pt-6">
                    <label class="flex items-center gap-2"><input type="checkbox" name="enable" value="1" @checked($cartDiscount->enable ?? false) class="checkbox checkbox-sm"> مفعّل</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="show_only_on_empty_cart" value="1" @checked($cartDiscount->show_only_on_empty_cart ?? false) class="checkbox checkbox-sm"> للسلة الفارغة فقط</label>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">حفظ</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'cart_discount']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
