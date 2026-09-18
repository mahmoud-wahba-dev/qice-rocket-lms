@extends('panel_v1.admin.layouts.app')

@section('content')
@php $isEdit = isset($testimonial) && $testimonial; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => $isEdit ? 'تعديل رأي' : 'رأي جديد',
        'subtitle' => 'منطق Admin\TestimonialsController حرفياً — المستخدم + التقييم + التعليق',
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
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة *</label>
                    <select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="active" @selected(old('status', $testimonial->status ?? 'active')=='active')>نشط</option>
                        <option value="disable" @selected(old('status', $testimonial->status ?? '')=='disable')>معطل</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">اسم المستخدم *</label>
                    <input type="text" name="user_name" value="{{ old('user_name', $testimonial->user_name ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: أحمد محمد">
                    @error('user_name')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الصورة الرمزية *</label>
                    <input type="text" name="user_avatar" value="{{ old('user_avatar', $testimonial->user_avatar ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="رابط الصورة">
                    @error('user_avatar')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">نبذة المستخدم *</label>
                    <input type="text" name="user_bio" value="{{ old('user_bio', $testimonial->user_bio ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: طالب في دورة...">
                    @error('user_bio')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">التقييم (0-5) *</label>
                    <input type="number" name="rate" value="{{ old('rate', $testimonial->rate ?? 5) }}" required min="0" max="5" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                    @error('rate')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">التعليق *</label>
                    <textarea name="comment" rows="4" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3" placeholder="نص الرأي">{{ old('comment', $testimonial->comment ?? '') }}</textarea>
                    @error('comment')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الرأي' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.testimonials') }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
