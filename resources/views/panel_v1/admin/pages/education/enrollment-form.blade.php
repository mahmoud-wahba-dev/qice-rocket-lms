@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => 'إضافة طالب لدورة',
        'subtitle' => 'تسجيل يدوي — منطق Admin\EnrollmentController حرفياً (user_id + عنصر واحد)',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction ?? route('panel.v1.admin.education.enrollment.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">معرف الطالب (user_id) *</label>
                    <input type="number" name="user_id" value="{{ old('user_id') }}" required min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: 123">
                    @error('user_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">معرف الدورة (webinar_id)</label>
                    <input type="number" name="webinar_id" value="{{ old('webinar_id') }}" min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="اختياري — دورة واحدة فقط">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">معرف الحزمة (bundle_id)</label>
                    <input type="number" name="bundle_id" value="{{ old('bundle_id') }}" min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="اختياري">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">معرف المنتج (product_id)</label>
                    <input type="number" name="product_id" value="{{ old('product_id') }}" min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="اختياري">
                </div>
            </div>
            <p class="font-medium text-12px text-gray leading-relaxed">* املأ عنصراً واحداً فقط (دورة أو حزمة أو منتج) — الأولوية للدورة ثم الحزمة ثم المنتج كما في الكنترولر.</p>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إضافة التسجيل</button>
                <a href="{{ route('panel.v1.admin.education.enrollment.history') }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
