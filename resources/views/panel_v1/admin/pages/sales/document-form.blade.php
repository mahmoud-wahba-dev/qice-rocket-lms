@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'مستند مالي جديد','subtitle'=>'منطق Admin\\DocumentController حرفياً — عملة + مبلغ + مستخدم'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction ?? route('panel.v1.admin.sales.documents.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">المستخدم *</label><input type="number" name="user_id" value="{{ old('user_id') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="ID المستخدم">@error('user_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">نوع الحركة *</label><select name="type" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px" required><option value="addiction" @selected(old('type')=='addiction')>إيداع (إضافة)</option><option value="deduction" @selected(old('type')=='deduction')>سحب (خصم)</option></select>@error('type')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">العملة *</label><input type="text" name="currency" value="{{ old('currency', 'SAR') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="SAR / USD">@error('currency')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">المبلغ *</label><input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">@error('amount')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف</label><textarea name="description" rows="3" class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px">{{ old('description') }}</textarea></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء المستند</button>
                <a href="{{ route('panel.v1.admin.sales.section',['section'=>'documents']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
