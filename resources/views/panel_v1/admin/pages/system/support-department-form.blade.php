@extends('panel_v1.admin.layouts.app')

@section('content')
@php $isEdit = isset($department) && $department; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => $isEdit ? 'تعديل قسم دعم' : 'قسم دعم جديد',
        'subtitle' => 'منطق Admin\SupportDepartmentsController حرفياً — عنوان + أيقونة + لون',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label>
                    <select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="ar" @selected(old('locale', app()->getLocale())=='ar')>العربية</option>
                        <option value="en" @selected(old('locale')=='en')>English</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label>
                    <input type="text" name="title" value="{{ old('title', $department->title ?? '') }}" required minlength="2" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: الدعم الفني">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الأيقونة *</label>
                    <input type="text" name="icon" value="{{ old('icon', $department->icon ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="icon-[tabler--headset]">
                    @error('icon')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">اللون *</label>
                    <input type="color" name="color" value="{{ old('color', $department->color ?? '#0F3D36') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 bg-white p-1">
                    @error('color')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء القسم' }}</button>
                <a href="{{ route('panel.v1.admin.system.section',['section'=>'support_departments']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
