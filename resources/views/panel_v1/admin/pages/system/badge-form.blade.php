@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'إنشاء شارة جديدة','subtitle'=>'منطق Admin\BadgesController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">عنوان الشارة *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: المتميز الذهبي">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الوصف *</label>
                    <textarea name="description" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <input type="text" name="type" value="{{ old('type', 'registration') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: registration" dir="ltr">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النقاط</label>
                    <input type="number" name="score" value="{{ old('score') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء الشارة</button>
                <a href="{{ route('panel.v1.admin.system.section',['section'=>'badges']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
