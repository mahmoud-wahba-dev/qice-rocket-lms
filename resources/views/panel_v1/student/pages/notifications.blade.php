@extends('panel_v1.student.layouts.app')

@section('content')
@php
    $noDataImg = $panelStudentImg . '/no-data.webp';
    $hasNotifications = $hasNotifications ?? true;
    $notifications = $notifications ?? collect();
@endphp

<section class="pt-28 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">الاشعارات</h1>
            <p class="font-semibold text-24px text-gray">اطلع علي جميع اشعاراتك هنا</p>
        </div>

        <div
            class="student-notifications-toolbar bg-[#F9F5F5] flex flex-wrap items-center justify-between gap-4 rounded-8px px-7 py-4 mb-11">
            <span class="font-semibold text-24px text-primary">إدارة الإشعارات</span>

            <form method="POST" action="{{ route('panel.v1.student.notifications.mark-all-read') }}">
                @csrf
                <button type="submit"
                    class="font-bold text-20px text-color2 underline underline-offset-4 hover:opacity-80 transition bg-transparent border-0 cursor-pointer p-0">
                    وضع علامة مقروء على الكل
                </button>
            </form>
        </div>

        @if ($hasNotifications)
            <div class="flex flex-col gap-5">
                @forelse ($notifications as $notification)
                    <article
                        class="student-notification-card flex flex-wrap items-center justify-between gap-6 border border-d9 rounded-16px bg-white px-6 py-5">
                        <div class="flex items-center gap-5 min-w-0 flex-1">
                            <div
                                class="student-notification-card__icon bg-[#F0FDF4] border-[#35655A] relative shrink-0 size-14 rounded-12px center">
                                <span class="icon-[tabler--bell] size-7 text-primary"></span>
                                @if (empty($notification->is_seen))
                                    <span
                                        class="student-notification-card__unread absolute -top-1 -end-1 size-3 rounded-full bg-[#EF4444]"
                                        aria-label="غير مقروء"></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-bold text-20px text-primary mb-1 leading-snug">
                                    {{ $notification->title }}
                                </h2>
                                <p class="font-medium text-13px text-gray leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($notification->message ?? ''), 160) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-8 shrink-0 ms-auto">
                            <div class="text-center">
                                <p class="font-bold text-16px text-[#575757] mb-1">
                                    {{ date('Y/m/d', (int) $notification->created_at) }}</p>
                                <p class="font-medium text-16px text-[#464646]">
                                    {{ date('H:i', (int) $notification->created_at) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="mt-16 center flex-col text-center">
                        <p class="font-semibold text-32px text-black">لا توجد إشعارات حالياً</p>
                    </div>
                @endforelse
            </div>
        @else
            {{-- Empty state — shown when there are no notifications --}}
            <div class="mt-16 center flex-col text-center">
                <div class="mb-8">
                    <img src="{{ $noDataImg }}" alt="لا توجد إشعارات حالياً" class="max-w-xs w-full mx-auto"
                        width="" height="" loading="lazy" decoding="async">
                </div>
                <p class="font-semibold text-32px text-black">لا توجد إشعارات حالياً</p>
            </div>
        @endif
    </div>
</section>
@endsection
