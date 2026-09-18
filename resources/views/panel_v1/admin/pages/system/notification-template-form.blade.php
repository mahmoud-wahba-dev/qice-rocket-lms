@extends('panel_v1.admin.layouts.app')

@section('content')
@php $isEdit = isset($template) && $template; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => $isEdit ? 'تعديل قالب إشعار' : 'قالب إشعار جديد',
        'subtitle' => 'منطق Admin\NotificationTemplatesController حرفياً — عنوان + نص القالب',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label>
                <input type="text" name="title" value="{{ old('title', $template->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: إشعار بدء الدورة">
                @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">نص القالب *</label>
                <textarea name="template" rows="5" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3" placeholder="نص الإشعار — يمكن استعمال المتغيرات مثل [user_name]">{{ old('template', $template->template ?? '') }}</textarea>
                @error('template')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء القالب' }}</button>
                <a href="{{ route('panel.v1.admin.system.section',['section'=>'notification_templates']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
