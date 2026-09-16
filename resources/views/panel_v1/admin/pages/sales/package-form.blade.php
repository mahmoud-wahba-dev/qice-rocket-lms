@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($package) && $package; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل باقة' : 'باقة تسجيل جديدة','subtitle'=>'منطق Admin\RegistrationPackagesController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><input type="text" name="locale" value="{{ old('locale', app()->getLocale()) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الدور *</label>
                    <select name="role" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="instructors" @selected(old('role', $package->role ?? '')=='instructors')>مدربين</option>
                        <option value="organizations" @selected(old('role', $package->role ?? '')=='organizations')>منظمات</option>
                    </select>
                </div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $package->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">الوصف *</label><textarea name="description" rows="3" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description', $package->description ?? '') }}</textarea></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيام *</label><input type="number" name="days" value="{{ old('days', $package->days ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">السعر *</label><input type="number" step="0.01" name="price" value="{{ old('price', $package->price ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيقونة *</label><input type="text" name="icon" value="{{ old('icon', $package->icon ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الحالة</label><select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white"><option value="active" @selected(old('status', $package->status ?? '')=='active')>نشط</option><option value="disabled" @selected(old('status', $package->status ?? '')=='disabled')>معطل</option></select></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.sales.section',['section'=>'packages']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
