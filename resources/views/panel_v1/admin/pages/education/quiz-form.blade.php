@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'إنشاء اختبار جديد','subtitle'=>'منطق Admin\QuizController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">عنوان الاختبار *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: اختبار الوحدة الأولى">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الدورة *</label>
                    <select name="webinar_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="">اختر دورة</option>
                        @foreach ($webinars as $w)<option value="{{ $w['id'] }}" @selected(old('webinar_id')==$w['id'])>{{ $w['title'] }} (#{{ $w['id'] }})</option>@endforeach
                    </select>
                    @error('webinar_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">درجة النجاح *</label>
                    <input type="number" name="pass_mark" value="{{ old('pass_mark', 50) }}" min="0" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المدة (دقيقة)</label>
                    <input type="number" name="time" value="{{ old('time') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="فارغ = مفتوح">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المحاولات</label>
                    <input type="number" name="attempt" value="{{ old('attempt') }}" min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة *</label>
                    <select name="status" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="active" selected>نشط</option>
                        <option value="inactive">معطل</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء الاختبار</button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'quizzes']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
