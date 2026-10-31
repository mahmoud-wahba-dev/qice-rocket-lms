@extends('landing_v1.layouts.app')

@php
    $landingImg = asset('assets/landing_v1/img');
    $isPaid = $order->status === \App\Models\Order::$paid;
    $isOfflineSubmitted = !$isPaid && $order->status === \App\Models\Order::$paying;
    $isFailed = !$isPaid && !$isOfflineSubmitted;
    $panelHome = panelV1HomeUrl(auth()->user());
    $heroSrc = $landingImg . '/payment/' . ($isFailed ? 'failed' : 'success') . '.jpg';

    if ($isPaid) {
        $title = trans('update.successful_payment');
        $hint = trans('update.successful_payment_hint');
        $badge = 'تم بنجاح';
    } elseif ($isOfflineSubmitted) {
        $title = trans('update.offline_payment_submitted');
        $hint = trans('update.offline_payment_submitted_hint');
        $badge = 'بانتظار المراجعة';
    } else {
        $title = trans('update.failed_payment');
        $hint = trans('update.failed_payment_hint');
        $badge = 'لم يكتمل';
    }
@endphp

@section('content')
<main class="relative min-h-[70vh] overflow-hidden bg-secondary pt-28 pb-20">
    {{-- Atmosphere --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-24 -start-24 size-[28rem] rounded-full bg-primary/[0.06] blur-3xl animate-[pulse_8s_ease-in-out_infinite]"></div>
        <div class="absolute -bottom-32 -end-20 size-[24rem] rounded-full bg-gold/30 blur-3xl animate-[pulse_10s_ease-in-out_infinite]"></div>
        <div class="absolute top-1/3 start-1/2 size-64 -translate-x-1/2 rounded-full bg-primary/[0.03] blur-2xl"></div>
    </div>

    <div class="container relative z-10 mx-auto max-w-xl px-4">
        <section
            @class([
                'payment-status-card mx-auto overflow-hidden rounded-[24px] border border-primary/5 bg-white/90 text-center shadow-[0_24px_60px_-28px_rgba(15,76,69,0.35)] backdrop-blur-sm',
            ])
            data-state="{{ $isPaid ? 'paid' : ($isOfflineSubmitted ? 'offline' : 'failed') }}"
        >
            <div class="relative px-6 pb-10 pt-10 sm:px-10 sm:pb-12 sm:pt-12">
                <div @class([
                    'mb-5 inline-flex items-center gap-2 rounded-full px-3 py-1 text-12px font-semibold',
                    'bg-primary/10 text-primary' => $isPaid,
                    'bg-gold/40 text-primary' => $isOfflineSubmitted,
                    'bg-rose-50 text-rose-800' => $isFailed,
                ])>
                    <span class="size-1.5 rounded-full bg-current opacity-70"></span>
                    {{ $badge }}
                </div>

                <div class="relative mx-auto mb-8 w-full max-w-[280px]">
                    <div @class([
                        'absolute inset-4 rounded-full ring-[12px] opacity-70 blur-[1px]',
                        'ring-primary/15' => $isPaid,
                        'ring-gold/40' => $isOfflineSubmitted,
                        'ring-rose-200' => $isFailed,
                    ])></div>
                    <img
                        src="{{ $heroSrc }}"
                        alt="{{ $title }}"
                        class="payment-status-hero relative z-10 mx-auto aspect-square w-full rounded-[20px] object-cover shadow-sm"
                        width="280"
                        height="280"
                    >
                </div>

                <h1 class="font-bold text-28px leading-tight text-primary sm:text-32px">
                    {{ $title }}
                </h1>

                <p class="mx-auto mt-3 max-w-md font-medium text-15px leading-7 text-primary/55">
                    {{ $hint }}
                </p>

                @if(!empty($order->id))
                    <p class="mt-4 font-medium text-13px text-primary/35">
                        رقم الطلب: <span class="text-primary/70" dir="ltr">#{{ $order->id }}</span>
                    </p>
                @endif

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                    <a
                        href="{{ $panelHome }}"
                        class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] bg-primary px-6 font-bold text-15px text-white transition hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                    >
                        {{ trans('public.my_panel') }}
                    </a>

                    @if($isFailed)
                        <a
                            href="{{ route('landing.v1.cart') }}"
                            class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] border border-primary/15 bg-f7 px-6 font-bold text-15px text-primary transition hover:border-primary/30 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20"
                        >
                            العودة للسلة
                        </a>
                    @elseif($isPaid)
                        <a
                            href="{{ route('landing.v1.index') }}"
                            class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] border border-primary/15 bg-f7 px-6 font-bold text-15px text-primary transition hover:border-primary/30 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20"
                        >
                            الرئيسية
                        </a>
                    @endif

                    @if($isPaid)
                        <a
                            href="academyapp://payment-success"
                            class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] border border-primary/10 px-6 font-semibold text-14px text-primary/70 transition hover:text-primary sm:hidden"
                        >
                            {{ trans('update.redirect_to_app') }}
                        </a>
                    @elseif($isFailed)
                        <a
                            href="academyapp://payment-failed"
                            class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] border border-primary/10 px-6 font-semibold text-14px text-primary/70 transition hover:text-primary sm:hidden"
                        >
                            {{ trans('update.redirect_to_app') }}
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<style>
    .payment-status-card {
        animation: paymentStatusIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .payment-status-hero {
        animation: paymentHeroIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.08s both;
    }
    @@keyframes paymentStatusIn {
        from { opacity: 0; transform: translateY(18px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @@keyframes paymentHeroIn {
        from { opacity: 0; transform: scale(0.92); }
        to { opacity: 1; transform: scale(1); }
    }
    @@media (prefers-reduced-motion: reduce) {
        .payment-status-card,
        .payment-status-hero {
            animation: none;
        }
    }
</style>
@endpush
