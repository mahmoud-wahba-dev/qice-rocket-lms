@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($field) && $field; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل حقل' : 'حقل جديد','subtitle'=>'منطق Admin\\FormFieldController حرفياً — نوع + مطلوب + ترجمة'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $field->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">@error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">النوع *</label><select name="type" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px" required><option value="text" @selected(old('type', $field->type ?? '')=='text')>نص</option><option value="number" @selected(old('type', $field->type ?? '')=='number')>رقم</option><option value="email" @selected(old('type', $field->type ?? '')=='email')>بريد إلكتروني</option><option value="textarea" @selected(old('type', $field->type ?? '')=='textarea')>نص طويل</option><option value="select" @selected(old('type', $field->type ?? '')=='select')>قائمة منسدلة</option><option value="checkbox" @selected(old('type', $field->type ?? '')=='checkbox')>اختيار متعدد</option></select>@error('type')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">النص التوضيحي (placeholder)</label><input type="text" name="placeholder" value="{{ old('placeholder', $field->placeholder ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="flex items-center gap-2 pt-6"><label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="required" value="on" @checked(old('required', $field->required ?? false)) class="checkbox checkbox-sm"> حقل مطلوب</label></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إضافة الحقل' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.form-fields',['formId'=>$formM->id]) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
