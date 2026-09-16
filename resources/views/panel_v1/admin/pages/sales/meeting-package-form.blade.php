@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($package) && $package; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل باقة اجتماعات' : 'باقة اجتماعات جديدة','subtitle'=>'منطق Admin\MeetingPackagesController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">المنشئ (معرّف المستخدم) *</label><input type="number" name="creator_id" value="{{ old('creator_id', $package->creator_id ?? auth()->id()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $package->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيقونة</label><input type="text" name="icon" value="{{ old('icon', $package->icon ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/assets/..."></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">المدة *</label><input type="number" name="duration" value="{{ old('duration', $package->duration ?? 30) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">نوع المدة *</label><select name="duration_type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="day" @selected(old('duration_type', $package->duration_type ?? 'month')=='day')>يوم</option><option value="week" @selected(old('duration_type', $package->duration_type ?? '')=='week')>أسبوع</option><option value="month" @selected(old('duration_type', $package->duration_type ?? '')=='month')>شهر</option><option value="year" @selected(old('duration_type', $package->duration_type ?? '')=='year')>سنة</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">عدد الجلسات *</label><input type="number" name="sessions" value="{{ old('sessions', $package->sessions ?? 5) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">مدة الجلسة (دقيقة) *</label><input type="number" name="session_duration" value="{{ old('session_duration', $package->session_duration ?? 30) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">السعر (ر.س)</label><input type="number" step="0.01" name="price" value="{{ old('price', $package->price ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="0 = مجاني"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الخصم %</label><input type="number" step="0.01" name="discount" value="{{ old('discount', $package->discount ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2 flex items-center gap-2 py-2"><label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="enable" value="on" @checked(old('enable', $package->enable ?? true)) class="checkbox checkbox-sm"> مفعّل</label></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الباقة' }}</button>
                <a href="{{ route('panel.v1.admin.sales.section',['section'=>'meetings']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
