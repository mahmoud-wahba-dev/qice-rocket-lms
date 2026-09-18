@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => 'منطقة جديدة',
        'subtitle' => 'منطق Admin\RegionController — النوع + الدولة الأب + الاسم',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" id="regionType" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="{{ \App\Models\Region::$country }}" @selected(old('type')==\App\Models\Region::$country)>دولة</option>
                        <option value="{{ \App\Models\Region::$province }}" @selected(old('type')==\App\Models\Region::$province)>محافظة</option>
                        <option value="{{ \App\Models\Region::$city }}" @selected(old('type')==\App\Models\Region::$city)>مدينة</option>
                        <option value="{{ \App\Models\Region::$district }}" @selected(old('type')==\App\Models\Region::$district)>حي</option>
                    </select>
                    @error('type')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div id="parentWrap">
                    <label class="font-semibold text-14px text-primary mb-2 block">الدولة الأب</label>
                    <select name="parent_id" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">بدون أب (للدول)</option>
                        @foreach($countries ?? [] as $c)
                            <option value="{{ $c->id }}" @selected(old('parent_id')==$c->id)>{{ $c->title ?? 'دولة #'.$c->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الاسم *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: الرياض">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء المنطقة</button>
                <a href="{{ route('panel.v1.admin.system.section',['section'=>'region']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
