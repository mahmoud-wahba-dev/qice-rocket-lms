@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($notification) && $notification; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل إشعار شراء' : 'إشعار شراء جديد','subtitle'=>'منطق Admin\PurchaseNotificationsController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $notification->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">عنوان النافذة *</label><input type="text" name="popup_title" value="{{ old('popup_title', $notification->popup_title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">وصف النافذة</label><input type="text" name="popup_subtitle" value="{{ old('popup_subtitle', $notification->popup_subtitle ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">مدة الظهور (ثوانٍ) *</label><input type="number" name="popup_duration" value="{{ old('popup_duration', $notification->popup_duration ?? 5) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">تأخير (ثوانٍ) *</label><input type="number" name="popup_delay" value="{{ old('popup_delay', $notification->popup_delay ?? 2) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">وقت العرض</label><input type="number" name="display_time" value="{{ old('display_time', $notification->display_time ?? 10) }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="flex items-center gap-2 py-2"><label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="enable" value="on" @checked(old('enable', $notification->enable ?? true)) class="checkbox checkbox-sm"> مفعّل</label></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'purchase_notifications']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
