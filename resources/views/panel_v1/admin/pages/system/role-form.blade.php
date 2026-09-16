@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'إنشاء دور جديد','subtitle'=>'منطق Admin\RoleController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الاسم البرمجي *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: support_agent" dir="ltr">
                    @error('name')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">العنوان المعروض *</label>
                    <input type="text" name="caption" value="{{ old('caption') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: موظف دعم">
                    @error('caption')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="is_admin" value="on" class="checkbox checkbox-sm"> دور إداري (is_admin)</label>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء الدور</button>
                <a href="{{ route('panel.v1.admin.system.section',['section'=>'roles']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
