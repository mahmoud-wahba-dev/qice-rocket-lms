@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $notifications = $notifications ?? collect();
    $hasNotifications = $hasNotifications ?? $notifications->isNotEmpty();
@endphp

<section>
    <div class="mb-8">
        <h1 class="font-extrabold text-28px sm:text-32px text-primary mb-2">الإشعارات</h1>
        <p class="font-medium text-15px sm:text-16px text-gray">اطلع على جميع إشعاراتك هنا</p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-12px border border-d9 bg-white px-5 sm:px-7 py-4 mb-6">
        <span class="font-bold text-18px sm:text-20px text-primary">إدارة الإشعارات</span>
        @if ($hasNotifications)
            <form method="POST" action="{{ route('panel.v1.instructor.notifications.mark-all-read') }}">
                @csrf
                <button type="submit"
                    class="font-bold text-14px sm:text-15px text-color2 underline underline-offset-4 hover:opacity-80 transition bg-transparent border-0 cursor-pointer p-0">
                    وضع علامة مقروء على الكل
                </button>
            </form>
        @endif
    </div>

    @if ($hasNotifications)
        <div class="flex flex-col gap-4">
            @foreach ($notifications as $notification)
                <article class="flex flex-wrap items-center justify-between gap-4 border border-d9 rounded-16px bg-white px-5 sm:px-6 py-4 sm:py-5 {{ empty($notification->is_seen) ? 'ring-1 ring-primary/15' : '' }}">
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        <div class="relative shrink-0 size-12 sm:size-14 rounded-12px center bg-primary/10 border border-primary/20">
                            <span class="icon-[tabler--bell] size-6 sm:size-7 text-primary"></span>
                            @if (empty($notification->is_seen))
                                <span class="absolute -top-1 -end-1 size-3 rounded-full bg-[#EF4444]" aria-label="غير مقروء"></span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-bold text-16px sm:text-18px text-primary mb-1 leading-snug">
                                {{ $notification->title }}
                            </h2>
                            <p class="font-medium text-13px text-gray leading-relaxed">
                                {{ \Illuminate\Support\Str::limit(strip_tags($notification->message ?? ''), 180) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-center shrink-0 ms-auto">
                        <p class="font-bold text-14px text-[#575757] mb-0.5">
                            {{ date('Y/m/d', (int) $notification->created_at) }}
                        </p>
                        <p class="font-medium text-13px text-[#464646]">
                            {{ date('H:i', (int) $notification->created_at) }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="rounded-16px border border-d9 bg-white px-6 py-16 center flex-col text-center">
            <span class="size-14 rounded-full bg-primary/10 center mb-4">
                <span class="icon-[tabler--bell-off] size-7 text-primary/50"></span>
            </span>
            <p class="font-bold text-20px text-primary mb-1">لا توجد إشعارات حالياً</p>
            <p class="font-medium text-14px text-gray">ستظهر هنا إشعارات المبيعات والتكليفات والنظام</p>
        </div>
    @endif
</section>
@endsection
