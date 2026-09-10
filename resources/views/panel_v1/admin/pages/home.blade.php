@extends('panel_v1.admin.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">لوحة الإدارة</h1>
            <p class="font-semibold text-20px text-gray">نظرة عامة على المنصة — الإدارة التفصيلية في <a href="{{ getAdminPanelUrl('/') }}" class="text-primary font-bold">اللوحة القديمة</a></p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @foreach ($stats ?? [] as $stat)
                <div class="border border-d9 rounded-16px bg-white px-8 py-8 text-center">
                    <p class="font-bold text-36px text-primary leading-none mb-3">{{ $stat['value'] }}</p>
                    <p class="font-semibold text-18px text-gray">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="border border-d9 rounded-16px bg-white px-8 py-8">
                <h2 class="font-bold text-22px text-primary mb-6">أحدث المبيعات</h2>
                @forelse ($recentSales ?? [] as $sale)
                    <div class="flex items-center justify-between gap-3 border-b border-d9 last:border-0 py-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-16px text-primary truncate">{{ $sale->buyer->full_name ?? '' }}</p>
                            <p class="font-medium text-13px text-gray truncate">{{ $sale->webinar->title ?? $sale->type }}</p>
                        </div>
                        <p class="font-bold text-15px text-primary shrink-0">{{ handlePrice($sale->total_amount) }}</p>
                    </div>
                @empty
                    <p class="font-medium text-15px text-gray">لا توجد مبيعات بعد.</p>
                @endforelse
            </div>

            <div class="border border-d9 rounded-16px bg-white px-8 py-8">
                <h2 class="font-bold text-22px text-primary mb-6">تذاكر مفتوحة</h2>
                @forelse ($openTickets ?? [] as $ticket)
                    <div class="flex items-center justify-between gap-3 border-b border-d9 last:border-0 py-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-16px text-primary truncate">{{ $ticket->title }}</p>
                            <p class="font-medium text-13px text-gray">{{ $ticket->user->full_name ?? '' }} — {{ date('Y/m/d', (int) $ticket->created_at) }}</p>
                        </div>
                        <a href="{{ url(getAdminPanelUrl('/supports/' . $ticket->id . '/conversation')) }}"
                            class="font-bold text-14px px-4 py-2 rounded-8px bg-[#E8F5E9] text-[#00B31B] shrink-0">عرض</a>
                    </div>
                @empty
                    <p class="font-medium text-15px text-gray">لا توجد تذاكر مفتوحة.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
