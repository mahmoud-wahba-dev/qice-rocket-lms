@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'إشعار طلاب الدورة','subtitle'=>$webinar->title.' ('.$studentsCount.' طالب)'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">عنوان الإشعار *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none">
                @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">نص الرسالة *</label>
                <textarea name="message" rows="4" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إرسال إلى {{ $studentsCount }} طالب</button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'courses']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
        <p class="font-medium text-12px text-gray mt-6 leading-relaxed">* نسخة من <code>Admin\WebinarController::sendNotificationToStudents:1170</code> — إشعار داخلي فقط (البريد للإنتاج فقط كما في الأصل).</p>
    </div>
</div>
@endsection
