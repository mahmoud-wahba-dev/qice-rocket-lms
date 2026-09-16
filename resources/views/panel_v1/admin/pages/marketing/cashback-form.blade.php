@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($rule) && $rule; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل كاش باك' : 'قاعدة كاش باك جديدة','subtitle'=>'منطق Admin\CashbackRuleController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $rule->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">نوع الهدف *</label><select name="target_type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="all_courses">كل الدورات</option><option value="specific_categories">تصنيفات محددة</option><option value="specific_courses">دورات محددة</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">المبلغ *</label><input type="number" step="0.01" name="amount" value="{{ old('amount', $rule->amount ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">نوع المبلغ *</label><select name="amount_type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="fixed_amount" @selected(old('amount_type', $rule->amount_type ?? 'fixed_amount')=='fixed_amount')>مبلغ ثابت</option><option value="percent" @selected(old('amount_type', $rule->amount_type ?? '')=='percent')>نسبة مئوية</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">تاريخ البدء *</label><input type="datetime-local" name="start_date" value="{{ old('start_date', isset($rule) ? date('Y-m-d\TH:i', (int)$rule->start_date) : '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">تاريخ الانتهاء</label><input type="datetime-local" name="end_date" value="{{ old('end_date', isset($rule) && $rule->end_date ? date('Y-m-d\TH:i', (int)$rule->end_date) : '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2 flex items-center gap-2 py-2"><label class="flex items-center gap-2"><input type="checkbox" name="enable" value="on" @checked(old('enable', $rule->enable ?? true)) class="checkbox checkbox-sm"> مفعّل</label></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'cashback']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
