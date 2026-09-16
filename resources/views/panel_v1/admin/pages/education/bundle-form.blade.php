@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> ($bundle ?? null) ? 'تعديل حزمة' : 'إنشاء حزمة جديدة','subtitle'=>'منطق Admin\BundleController حرفياً — نفس تصميم course-form'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ ($bundle ?? null) ? route('panel.v1.admin.education.bundles.update',['id'=>$bundle->id]) : route('panel.v1.admin.education.bundles.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">عنوان الحزمة *</label>
                    <input type="text" name="title" value="{{ old('title', $bundle->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: باقة المبتدئين">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المدرب *</label>
                    <select name="teacher_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="">اختر مدرب</option>
                        @foreach ($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id', $bundle->teacher_id ?? '')==$t->id)>{{ $t->full_name }} (#{{ $t->id }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">التصنيف *</label>
                    <select name="category_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="">اختر تصنيف</option>
                        @foreach ($categories as $c)<option value="{{ $c['id'] }}" @selected(old('category_id', $bundle->category_id ?? '')==$c['id'])>{{ $c['title'] }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">السعر (ر.س)</label>
                    <input type="number" name="price" value="{{ old('price', isset($bundle->price) ? (int)$bundle->price : '') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="0 = مجانية">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة</label>
                    <select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="pending" @selected(old('status', $bundle->status ?? 'pending')=='pending')>بانتظار المراجعة</option>
                        <option value="active" @selected(old('status', $bundle->status ?? '')=='active')>نشط</option>
                        <option value="is_draft" @selected(old('status', $bundle->status ?? '')=='is_draft')>مسودة</option>
                        <option value="inactive" @selected(old('status', $bundle->status ?? '')=='inactive')>مرفوض</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ ($bundle ?? null) ? 'حفظ التعديلات' : 'إنشاء الحزمة' }}</button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'bundles']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
