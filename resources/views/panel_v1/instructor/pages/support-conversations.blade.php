@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $authUser = $authUser ?? auth()->user();
    $supports = $supports ?? collect();
    $selectSupport = $selectSupport ?? null;
    $isTicketMode = $isTicketMode ?? false;

    $statusLabel = function (?string $status): string {
        return match ($status) {
            'close' => 'مغلقة',
            'supporter_replied' => 'تم الرد',
            default => 'انتظار',
        };
    };

    $statusClasses = function (?string $status): string {
        return match ($status) {
            'close' => 'bg-[#F3F4F6] text-[#6B7280]',
            'supporter_replied' => 'bg-[#DBEAFE] text-[#1D4ED8]',
            default => 'bg-[#FEF3C7] text-[#B45309]',
        };
    };

    $initials = function ($person): string {
        $name = trim((string) ($person->full_name ?? ''));
        if ($name === '') {
            return '؟';
        }
        $parts = preg_split('/\s+/u', $name) ?: [];
        $chars = [];
        foreach (array_slice($parts, 0, 2) as $part) {
            $chars[] = mb_substr($part, 0, 1);
        }

        return implode('', $chars) ?: '؟';
    };

    $listPerson = function ($support) use ($authUser) {
        if (!empty($support->webinar) && (int) ($support->webinar->teacher_id ?? 0) !== (int) $authUser->id) {
            return $support->webinar->teacher ?? $support->user;
        }

        return $support->user;
    };

    $avatarColors = ['bg-[#DBEAFE] text-[#1D4ED8]', 'bg-[#E0E7FF] text-[#4338CA]', 'bg-[#FCE7F3] text-[#BE185D]', 'bg-[#D1FAE5] text-[#047857]', 'bg-[#FEF3C7] text-[#B45309]'];
@endphp

<div class="space-y-6 sm:space-y-8 pb-10" data-instructor-support-chat>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('panel.v1.instructor.support') }}"
                class="inline-flex items-center gap-2 font-bold text-14px text-primary hover:opacity-80 mb-3">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة لمركز الدعم
            </a>
            <h1 class="font-semibold text-28px sm:text-32px text-primary">
                {{ $isTicketMode ? 'تذاكر الدعم' : 'دعم الصفوف' }}
            </h1>
            <p class="font-medium text-15px sm:text-16px text-gray mt-1">
                المحادثات والردود في واجهة واحدة بنفس منطق النظام السابق.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-12px border border-d9 bg-white px-4 py-2 font-medium text-13px text-gray">
                الكل <strong class="text-primary">{{ $supportsCount ?? 0 }}</strong>
            </span>
            <span class="inline-flex items-center gap-2 rounded-12px border border-d9 bg-white px-4 py-2 font-medium text-13px text-gray">
                مفتوحة <strong class="text-[#B45309]">{{ $openSupportsCount ?? 0 }}</strong>
            </span>
            <span class="inline-flex items-center gap-2 rounded-12px border border-d9 bg-white px-4 py-2 font-medium text-13px text-gray">
                مغلقة <strong class="text-gray">{{ $closeSupportsCount ?? 0 }}</strong>
            </span>
        </div>
    </div>

    @if ($supports->isEmpty())
        <div class="rounded-20px border border-d9 bg-white px-6 py-20 text-center shadow-sm">
            <span class="size-16 rounded-full bg-fa border border-d9 center mx-auto mb-5">
                <span class="icon-[tabler--messages] size-8 text-primary"></span>
            </span>
            <h2 class="font-bold text-20px text-primary mb-2">لا توجد محادثات حالياً</h2>
            <p class="font-medium text-15px text-gray mb-6">عند وصول رسائل دعم جديدة ستظهر هنا.</p>
            <a href="{{ route('panel.v1.instructor.support') }}" class="btn btn-primary rounded-12px h-12 px-8 font-bold text-15px">
                مركز الدعم
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
            {{-- Sidebar conversations --}}
            <aside class="lg:col-span-4 xl:col-span-4 order-2 lg:order-1">
                <div class="rounded-20px border border-d9 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 pt-5 pb-4 border-b border-d9">
                        <h2 class="font-bold text-18px text-primary mb-4">المحادثات</h2>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="icon-[tabler--search] size-4 text-gray absolute start-3 top-1/2 -translate-y-1/2 pointer-events-none"></span>
                                <input type="search" id="js-support-search" placeholder="بحث..."
                                    class="input input-bordered w-full h-11 rounded-12px border-d9 bg-fa ps-10 pe-3 font-medium text-14px focus:outline-none focus:border-primary">
                            </div>
                            <div class="dropdown relative">
                                <button type="button" class="btn btn-ghost size-11 min-h-11 rounded-12px border border-d9 bg-fa p-0"
                                    aria-label="تصفية"
                                    data-support-filter-toggle>
                                    <span class="icon-[tabler--adjustments-horizontal] size-5 text-gray"></span>
                                </button>
                                <div id="js-support-filter-menu"
                                    class="hidden absolute end-0 top-full mt-2 z-20 w-48 rounded-12px border border-d9 bg-white shadow-lg py-2">
                                    <button type="button" data-status="all" class="js-support-status-filter w-full text-start px-4 py-2.5 font-medium text-14px text-primary hover:bg-fa">كل التذاكر</button>
                                    <button type="button" data-status="replied" class="js-support-status-filter w-full text-start px-4 py-2.5 font-medium text-14px text-primary hover:bg-fa">تم الرد</button>
                                    <button type="button" data-status="open" class="js-support-status-filter w-full text-start px-4 py-2.5 font-medium text-14px text-primary hover:bg-fa">انتظار</button>
                                    <button type="button" data-status="close" class="js-support-status-filter w-full text-start px-4 py-2.5 font-medium text-14px text-primary hover:bg-fa">مغلقة</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto">
                        @foreach ($supports as $support)
                            @php
                                $person = $listPerson($support);
                                $last = $support->conversations->first();
                                $active = !empty($selectSupport) && (int) $selectSupport->id === (int) $support->id;
                                $color = $avatarColors[$loop->index % count($avatarColors)];
                                $snippet = $last->message ?? $support->title;
                                $filterStatus = $support->status === 'supporter_replied' ? 'replied' : ($support->status ?: 'open');
                            @endphp
                            <a href="{{ route('panel.v1.instructor.support.conversations', ['id' => $support->id]) }}"
                                class="js-support-list-item block px-5 py-4 border-b border-d9 last:border-0 transition {{ $active ? 'bg-[#F0F7F5]' : 'hover:bg-fa' }}"
                                data-status="{{ $filterStatus }}"
                                data-search="{{ mb_strtolower(($person->full_name ?? '') . ' ' . ($support->title ?? '') . ' ' . ($snippet ?? '')) }}">
                                <div class="flex items-start gap-3">
                                    <span class="size-12 rounded-full {{ $color }} center font-bold text-14px shrink-0">
                                        {{ $initials($person) }}
                                    </span>
                                    <div class="min-w-0 flex-1 text-start">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <h3 class="font-bold text-15px text-primary truncate">{{ $person->full_name ?? '—' }}</h3>
                                        </div>
                                        <p class="font-medium text-13px text-gray truncate mb-2">{{ \Illuminate\Support\Str::limit($snippet, 48) }}</p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-medium text-12px text-gray">
                                                {{ !empty($last) ? date('Y/m/d | H:i', (int) $last->created_at) : date('Y/m/d | H:i', (int) $support->created_at) }}
                                            </span>
                                            <span class="inline-flex rounded-8px px-2 py-0.5 font-semibold text-11px {{ $statusClasses($support->status) }}">
                                                {{ $statusLabel($support->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            {{-- Chat panel --}}
            <section class="lg:col-span-8 xl:col-span-8 order-1 lg:order-2 mt-0">
                <div class="rounded-20px border border-d9 bg-white shadow-sm overflow-hidden min-h-[640px] flex flex-col">
                    @if (!empty($selectSupport))
                        @php
                            $headerPerson = $selectSupport->user;
                            $headerColor = $avatarColors[((int) $selectSupport->id) % count($avatarColors)];
                        @endphp
                        <div class="px-5 sm:px-6 py-5 border-b border-d9 bg-fa/40">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug">{{ $selectSupport->title }}</h2>
                                @if (($selectSupport->status ?? '') !== 'close')
                                    <div class="dropdown relative shrink-0">
                                        <button type="button" class="btn btn-ghost btn-circle btn-sm" data-support-more-toggle aria-label="المزيد">
                                            <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                        </button>
                                        <div id="js-support-more-menu"
                                            class="hidden absolute end-0 top-full mt-1 z-20 w-44 rounded-12px border border-d9 bg-white shadow-lg py-2">
                                            <form method="POST" action="{{ route('panel.v1.instructor.support.close', ['id' => $selectSupport->id]) }}"
                                                onsubmit="return confirm('إغلاق هذه المحادثة؟');">
                                                @csrf
                                                <button type="submit" class="w-full text-start px-4 py-2.5 font-bold text-14px text-[#EF4444] hover:bg-[#FEF2F2]">
                                                    إغلاق المحادثة
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex rounded-8px px-3 py-1 font-semibold text-12px {{ $statusClasses('close') }}">مغلقة</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <span class="size-10 rounded-full {{ $headerColor }} center font-bold text-13px shrink-0">
                                    {{ $initials($headerPerson) }}
                                </span>
                                <div class="min-w-0 text-start">
                                    <p class="font-bold text-14px text-primary">{{ $headerPerson->full_name ?? '—' }}</p>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 font-medium text-12px text-gray">
                                        <span>أُنشئت: {{ date('Y/m/d | H:i', (int) $selectSupport->created_at) }}</span>
                                        @if (!empty($selectSupport->webinar))
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="icon-[tabler--school] size-4 text-primary"></span>
                                                {{ $selectSupport->webinar->title }}
                                            </span>
                                        @endif
                                        @if (!empty($selectSupport->department))
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="icon-[tabler--building] size-4 text-primary"></span>
                                                {{ $selectSupport->department->title }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="js-support-messages" class="flex-1 overflow-y-auto max-h-[48vh] lg:max-h-[52vh] p-5 sm:p-6 space-y-4">
                            @forelse ($selectSupport->conversations ?? [] as $conversation)
                                @php
                                    $conversationUser = !empty($conversation->supporter) ? $conversation->supporter : $conversation->sender;
                                    $roleLabel = !empty($conversation->supporter)
                                        ? 'staff'
                                        : (string) ($conversation->sender->role_name ?? 'user');
                                    $isMine = (int) ($conversation->sender_id ?? 0) === (int) $authUser->id;
                                    $msgColor = $avatarColors[((int) ($conversationUser->id ?? 0)) % count($avatarColors)];
                                @endphp
                                <div class="rounded-16px border border-d9 {{ $isMine ? 'bg-white' : 'bg-fa' }} p-4 sm:p-5">
                                    <div class="flex items-end justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="size-10 rounded-full {{ $msgColor }} center font-bold text-12px shrink-0 overflow-hidden">
                                                @if (!empty($conversationUser) && method_exists($conversationUser, 'getAvatar'))
                                                    <img src="{{ $conversationUser->getAvatar(40) }}" alt="" class="size-10 rounded-full object-cover"
                                                        onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden');">
                                                    <span class="hidden">{{ $initials($conversationUser) }}</span>
                                                @else
                                                    {{ $initials($conversationUser) }}
                                                @endif
                                            </span>
                                            <div class="min-w-0 text-start">
                                                <p class="font-bold text-14px text-primary truncate">{{ $conversationUser->full_name ?? '—' }}</p>
                                                <p class="font-medium text-12px text-gray">{{ $roleLabel }}</p>
                                            </div>
                                        </div>
                                        <span class="font-medium text-12px text-gray whitespace-nowrap shrink-0">
                                            {{ date('Y/m/d H:i', (int) $conversation->created_at) }}
                                        </span>
                                    </div>
                                    <p class="font-medium text-15px text-primary leading-relaxed whitespace-pre-wrap">{{ $conversation->message }}</p>
                                    @if (!empty($conversation->attach))
                                        <a href="{{ url($conversation->attach) }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-2 mt-3 rounded-10px border border-d9 bg-white px-3 py-2 font-bold text-13px text-primary hover:bg-fa">
                                            <span class="icon-[tabler--paperclip] size-4"></span>
                                            مرفق
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <p class="font-medium text-15px text-gray text-center py-12">لا توجد رسائل بعد.</p>
                            @endforelse
                        </div>

                        @if (($selectSupport->status ?? '') !== 'close')
                            <form method="POST"
                                action="{{ route('panel.v1.instructor.support.reply', ['id' => $selectSupport->id]) }}"
                                enctype="multipart/form-data"
                                class="border-t border-d9 p-4 sm:p-5 bg-white">
                                @csrf
                                <div class="flex items-center gap-3">
                                    <div class="relative flex-1">
                                        <input type="text" name="message" required autocomplete="off"
                                            value="{{ old('message') }}"
                                            placeholder="اكتب رسالتك..."
                                            class="input input-bordered w-full h-14 rounded-14px border-d9 font-medium text-15px focus:outline-none focus:border-primary @error('message') border-[#EF4444] @enderror">
                                        @error('message')
                                            <p class="absolute -bottom-5 start-1 font-medium text-12px text-[#EF4444]">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <input type="file" name="attach" id="js-support-attach" class="hidden">
                                    <button type="button" id="js-support-attach-btn"
                                        class="btn btn-ghost size-12 min-h-12 rounded-full border border-d9 bg-fa p-0 shrink-0"
                                        aria-label="إرفاق ملف">
                                        <span class="icon-[tabler--paperclip] size-5 text-gray"></span>
                                    </button>
                                    <button type="submit"
                                        class="btn btn-primary size-12 min-h-12 rounded-full p-0 shrink-0"
                                        aria-label="إرسال">
                                        <span class="icon-[tabler--send] size-5 text-white"></span>
                                    </button>
                                </div>
                                <p id="js-support-attach-name" class="hidden mt-2 font-medium text-12px text-gray truncate"></p>
                            </form>
                        @else
                            <div class="border-t border-d9 px-5 py-4 bg-[#FEF2F2] text-center">
                                <p class="font-bold text-14px text-[#B91C1C]">المحادثة مغلقة — لا يمكن إرسال ردود جديدة.</p>
                            </div>
                        @endif
                    @else
                        <div class="flex-1 center flex-col px-6 py-20 text-center">
                            <span class="size-16 rounded-full bg-fa border border-d9 center mb-5">
                                <span class="icon-[tabler--message-2] size-8 text-primary"></span>
                            </span>
                            <h2 class="font-bold text-20px text-primary mb-2">اختر محادثة</h2>
                            <p class="font-medium text-15px text-gray max-w-md">
                                اختر محادثة من القائمة لعرض الرسائل والرد عليها.
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var messages = document.getElementById('js-support-messages');
    if (messages) {
        messages.scrollTop = messages.scrollHeight;
    }

    var attachBtn = document.getElementById('js-support-attach-btn');
    var attachInput = document.getElementById('js-support-attach');
    var attachName = document.getElementById('js-support-attach-name');
    attachBtn?.addEventListener('click', function () { attachInput?.click(); });
    attachInput?.addEventListener('change', function () {
        if (!attachName) return;
        if (attachInput.files && attachInput.files[0]) {
            attachName.textContent = attachInput.files[0].name;
            attachName.classList.remove('hidden');
        } else {
            attachName.textContent = '';
            attachName.classList.add('hidden');
        }
    });

    var filterToggle = document.querySelector('[data-support-filter-toggle]');
    var filterMenu = document.getElementById('js-support-filter-menu');
    filterToggle?.addEventListener('click', function (e) {
        e.stopPropagation();
        filterMenu?.classList.toggle('hidden');
    });

    var moreToggle = document.querySelector('[data-support-more-toggle]');
    var moreMenu = document.getElementById('js-support-more-menu');
    moreToggle?.addEventListener('click', function (e) {
        e.stopPropagation();
        moreMenu?.classList.toggle('hidden');
    });

    document.addEventListener('click', function () {
        filterMenu?.classList.add('hidden');
        moreMenu?.classList.add('hidden');
    });

    var items = Array.prototype.slice.call(document.querySelectorAll('.js-support-list-item'));
    var searchInput = document.getElementById('js-support-search');
    var activeStatus = 'all';

    function applyFilters() {
        var q = (searchInput?.value || '').trim().toLowerCase();
        items.forEach(function (item) {
            var status = item.getAttribute('data-status') || 'open';
            var hay = item.getAttribute('data-search') || '';
            var statusOk = activeStatus === 'all' || status === activeStatus;
            var searchOk = !q || hay.indexOf(q) !== -1;
            item.classList.toggle('hidden', !(statusOk && searchOk));
        });
    }

    searchInput?.addEventListener('input', applyFilters);
    document.querySelectorAll('.js-support-status-filter').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeStatus = btn.getAttribute('data-status') || 'all';
            filterMenu?.classList.add('hidden');
            applyFilters();
        });
    });
});
</script>
@endpush
