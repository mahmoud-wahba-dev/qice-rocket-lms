@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($offer) && $offer; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل عرض خاص' : 'عرض خاص جديد','subtitle'=>'منطق Admin\SpecialOfferController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">الاسم</label><input type="text" name="name" value="{{ old('name', $offer->name ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">النسبة % *</label><input type="number" name="percent" value="{{ old('percent', $offer->percent ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">من تاريخ *</label><input type="datetime-local" name="from_date" value="{{ old('from_date', isset($offer) ? date('Y-m-d\TH:i', (int)$offer->from_date) : '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">إلى تاريخ *</label><input type="datetime-local" name="to_date" value="{{ old('to_date', isset($offer) ? date('Y-m-d\TH:i', (int)$offer->to_date) : '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">دورة</label><input type="number" name="webinar_id" value="{{ old('webinar_id', $offer->webinar_id ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="ID"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">حزمة</label><input type="number" name="bundle_id" value="{{ old('bundle_id', $offer->bundle_id ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="ID"></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">{{ $isEdit ? 'حفظ' : 'إنشاء' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'special_offers']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
