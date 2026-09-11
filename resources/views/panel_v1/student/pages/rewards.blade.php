@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">نقاطي ومكافآتي</h1>
            <p class="font-semibold text-20px text-gray">اكسب النقاط من التفاعل وحوّلها لرصيد</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4">
                <div class="rounded-16px bg-primary text-white p-8 relative overflow-hidden">
                    <div class="absolute -top-10 -end-10 size-32 rounded-full bg-white/10 blur-2xl"></div>
                    <p class="font-medium text-16px text-white/80 mb-2">رصيد النقاط</p>
                    <p class="font-extrabold text-48px leading-none">{{ $totalPoints ?? 0 }}</p>
                    <p class="font-medium text-13px text-white/70 mt-2">كل {{ $exchangeableUnit ?? 1 }} نقطة = {{ handlePrice($exchangeableWorth ?? 0) }}</p>
                </div>

                <div class="border border-d9 rounded-16px bg-white p-6 mt-6">
                    <h3 class="font-bold text-16px text-primary mb-4">تحويل النقاط لرصيد</h3>
                    <form method="POST" action="{{ route('panel.v1.student.rewards.exchange') }}" class="space-y-4">
                        @csrf
                        <input type="number" name="amount" min="1" max="{{ $totalPoints ?? 0 }}" placeholder="عدد النقاط" class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary">
                        <button type="submit" class="btn btn-primary w-full rounded-10px h-11 font-bold text-14px">تحويل</button>
                    </form>
                    <p class="font-medium text-12px text-gray mt-3">يجب أن يكون لديك رصيد كافٍ والحد الأدنى حسب إعدادات المنصة.</p>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="border border-d9 rounded-16px bg-white overflow-hidden">
                    <div class="px-6 py-4 border-b border-d9 bg-fa">
                        <h3 class="font-bold text-16px text-primary">سجل النقاط</h3>
                    </div>
                    @forelse ($accountings ?? [] as $acc)
                        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-d9 last:border-0">
                            <div>
                                <p class="font-bold text-14px {{ ($acc->score ?? 0) >=0 ? 'text-[#00B31B]' : 'text-[#EF4444]' }}">{{ ($acc->score ?? 0) >=0 ? '+' : '' }}{{ $acc->score }} نقطة</p>
                                <p class="font-medium text-12px text-gray">{{ $acc->type ?? '' }} — {{ date('Y/m/d', (int) $acc->created_at) }}</p>
                            </div>
                            <span class="font-medium text-13px text-gray">{{ $acc->description ?? '' }}</span>
                        </div>
                    @empty
                        <div class="px-8 py-16 center flex-col text-center">
                            <p class="font-semibold text-16px text-gray">لا يوجد سجل نقاط بعد</p>
                        </div>
                    @endforelse
                </div>

                @if (($availableRewards ?? collect())->isNotEmpty())
                    <div class="border border-d9 rounded-16px bg-white p-6 mt-6">
                        <h3 class="font-bold text-16px text-primary mb-4">مكافآت متاحة</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($availableRewards as $reward)
                                <div class="rounded-12px border border-d9 bg-fa/50 p-4">
                                    <p class="font-bold text-15px text-primary">{{ $reward->title ?? 'مكافأة #' . $reward->id }}</p>
                                    <p class="font-medium text-12px text-gray mt-1">النقاط المطلوبة: {{ $reward->score ?? $reward->point ?? '—' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
