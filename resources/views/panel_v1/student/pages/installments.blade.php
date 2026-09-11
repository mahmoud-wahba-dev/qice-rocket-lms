@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">الأقساط والباقات</h1>
            <p class="font-semibold text-20px text-gray">خطط التقسيط وباقات التسجيل الخاصة بك</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7">
                <div class="border border-d9 rounded-16px bg-white overflow-hidden">
                    <div class="px-6 py-4 border-b border-d9 bg-fa">
                        <h3 class="font-bold text-16px text-primary">طلبات التقسيط</h3>
                    </div>
                    @forelse ($orders ?? [] as $order)
                        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-d9 last:border-0">
                            <div>
                                <p class="font-bold text-14px text-primary">طلب #{{ $order->id }} — {{ $order->installment->title ?? '' }}</p>
                                <p class="font-medium text-12px text-gray">{{ $order->status ?? '' }} — {{ date('Y/m/d', (int) $order->created_at) }}</p>
                            </div>
                            <span class="font-bold text-13px text-primary">{{ handlePrice($order->amount ?? 0) }}</span>
                        </div>
                    @empty
                        <div class="px-8 py-12 center flex-col text-center">
                            <p class="font-semibold text-16px text-gray">لا توجد طلبات تقسيط</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="border border-d9 rounded-16px bg-white p-6">
                    <h3 class="font-bold text-16px text-primary mb-4">باقات التسجيل</h3>
                    @forelse ($userPackages ?? [] as $up)
                        <div class="rounded-12px border border-d9 bg-fa/50 px-4 py-3 mb-3">
                            <p class="font-bold text-14px text-primary">{{ $up->registrationPackage->title ?? 'باقة #' . $up->id }}</p>
                            <p class="font-medium text-12px text-gray">{{ date('Y/m/d', (int) $up->created_at) }} — {{ $up->status ?? '' }}</p>
                        </div>
                    @empty
                        <p class="font-medium text-14px text-gray">لم تشترك في أي باقة.</p>
                    @endforelse
                    @if (($packages ?? collect())->isNotEmpty())
                        <div class="mt-6">
                            <h4 class="font-bold text-14px text-primary mb-3">باقات متاحة</h4>
                            <div class="space-y-3">
                                @foreach ($packages as $pkg)
                                    <div class="rounded-12px border border-d9 px-4 py-3 flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-14px text-primary">{{ $pkg->title }}</p>
                                            <p class="font-medium text-12px text-gray">{{ handlePrice($pkg->price ?? 0) }}</p>
                                        </div>
                                        <a href="{{ url('/panel/financial/registration-packages') }}" class="font-bold text-13px text-primary hover:underline">عرض</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
