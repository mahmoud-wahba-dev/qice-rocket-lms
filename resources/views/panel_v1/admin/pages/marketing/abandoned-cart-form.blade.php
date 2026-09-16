@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($rule) && $rule; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل قاعدة سلة متروكة' : 'قاعدة سلة متروكة جديدة','subtitle'=>'منطق Admin\AbandonedCartRulesController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $rule->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">نوع الهدف *</label><input type="text" name="target_type" value="{{ old('target_type', $rule->target_type ?? 'all') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">دورة الإجراء *</label><input type="number" name="action_cycle" value="{{ old('action_cycle', $rule->action_cycle ?? 24) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="ساعات"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الإجراء</label>
                    <select name="action" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="send_coupon" @selected(old('action', $rule->action ?? '')=='send_coupon')>إرسال قسيمة</option>
                        <option value="send_reminder" @selected(old('action', $rule->action ?? '')=='send_reminder')>تذكير</option>
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2"><label class="flex items-center gap-2"><input type="checkbox" name="enable" value="1" @checked(old('enable', $rule->enable ?? true)) class="checkbox checkbox-sm"> مفعّل</label></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'abandoned_cart']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
