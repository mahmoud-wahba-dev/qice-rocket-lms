@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'تعديل قناة دفع','subtitle'=>'منطق Admin\\PaymentChannelController حرفياً — بيانات اعتماد + عملات'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title', $channel->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">@error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الحالة</label><select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px"><option value="active" @selected(old('status', $channel->status ?? '')=='active')>نشط</option><option value="inactive" @selected(old('status', $channel->status ?? '')=='inactive')>متوقف</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">صورة القناة</label><input type="text" name="image" value="{{ old('image', $channel->image ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                @if(!empty($credentialItems) && count($credentialItems))
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">بيانات الاعتماد (Credentials)</label></div>
                @foreach($credentialItems as $credItem)
                    <div><label class="font-semibold text-14px text-primary mb-2 block">{{ $credItem['key'] }}</label><input type="{{ $credItem['type'] ?? 'text' }}" name="credentials[{{ $credItem['key'] }}]" value="{{ old('credentials.'.$credItem['key'], '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                @endforeach
                @endif
                @if($showTestModeToggle ?? false)
                <div class="flex items-center gap-2"><label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="test_mode" value="on" @checked(old('test_mode', false)) class="checkbox checkbox-sm"> وضع الاختبار</label></div>
                @endif
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">حفظ التعديلات</button>
                <a href="{{ route('panel.v1.admin.sales.section',['section'=>'payment_channels']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
