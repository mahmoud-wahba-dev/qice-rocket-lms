{{-- Rocket-style bundle card adapted to panel_v1 instructor tokens --}}
@php
    $menuId = 'bundle-menu-' . ($index ?? ($bundle['id'] ?? 0));
    $status = $bundle['status'] ?? 'is_draft';
@endphp

<article class="relative z-0 hover:z-30 flex flex-col overflow-visible rounded-14px border border-d9 bg-white">
    <div class="relative h-44 sm:h-48 overflow-hidden rounded-t-14px bg-fa">
        @if (!empty($bundle['thumbnail']))
            <img src="{{ $bundle['thumbnail'] }}" alt="{{ $bundle['title'] }}"
                class="w-full h-full object-cover" loading="lazy">
        @else
            <div class="w-full h-full center bg-primary/10">
                <span class="icon-[tabler--package] size-12 text-primary/40"></span>
            </div>
        @endif
        <span @class([
            'absolute top-3 start-3 inline-flex items-center rounded-full px-2.5 py-1 font-semibold text-12px backdrop-blur-sm',
            'bg-white/90 text-primary' => $status === 'active',
            'bg-[#FFF7ED]/95 text-[#C2410C]' => $status === 'pending',
            'bg-white/90 text-gray' => in_array($status, ['is_draft', 'inactive'], true),
        ])>{{ $bundle['status_label'] ?? $status }}</span>
    </div>

    <div class="flex flex-col flex-1 p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="min-w-0 text-start">
                @if (!empty($bundle['public_url']) && $status === 'active')
                    <a href="{{ $bundle['public_url'] }}" target="_blank" rel="noopener"
                        class="font-bold text-16px sm:text-17px text-primary hover:opacity-80 transition line-clamp-2 leading-snug">
                        {{ $bundle['title'] }}
                    </a>
                @else
                    <h3 class="font-bold text-16px sm:text-17px text-primary line-clamp-2 leading-snug">{{ $bundle['title'] }}</h3>
                @endif

                <div class="flex items-center gap-1.5 mt-2">
                    <span class="icon-[tabler--star-filled] size-4 text-gold"></span>
                    <span class="font-semibold text-13px text-primary">{{ number_format((float) ($bundle['rate'] ?? 0), 1) }}</span>
                    <span class="font-medium text-12px text-gray">({{ (int) ($bundle['rate_count'] ?? 0) }})</span>
                </div>
            </div>

            <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end] shrink-0">
                <button type="button"
                    class="dropdown-toggle size-9 rounded-8px bg-fa !inline-flex !items-center !justify-center border border-d9 hover:bg-[#F1F5F9] transition"
                    aria-label="خيارات الحزمة" id="{{ $menuId }}">
                    <span class="icon-[tabler--dots] size-5 text-gray"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-52 py-2 rounded-12px border border-d9 bg-white shadow-xl z-50"
                    role="menu" aria-labelledby="{{ $menuId }}">
                    @if (!empty($bundle['edit_url']))
                        <li>
                            <a href="{{ $bundle['edit_url'] }}"
                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                <span class="icon-[tabler--pencil] size-4 text-gray shrink-0"></span>
                                تعديل الحزمة
                            </a>
                        </li>
                    @endif
                    @if (!empty($bundle['courses_url']))
                        <li>
                            <a href="{{ $bundle['courses_url'] }}"
                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                <span class="icon-[tabler--books] size-4 text-gray shrink-0"></span>
                                دورات الحزمة
                            </a>
                        </li>
                    @endif
                    @if (!empty($bundle['preview_url']))
                        <li>
                            <a href="{{ $bundle['preview_url'] }}"
                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                <span class="icon-[tabler--external-link] size-4 text-gray shrink-0"></span>
                                عرض عام
                            </a>
                        </li>
                    @endif
                    @if ($status !== 'inactive')
                        <li>
                            <form method="POST" action="{{ route('panel.v1.instructor.bundles.delete', ['id' => $bundle['id']]) }}"
                                onsubmit="return confirm('تعطيل هذه الحزمة؟');">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item flex w-full items-center gap-2 px-4 py-2.5 font-medium text-15px text-[#E11D48] text-start">
                                    <span class="icon-[tabler--trash] size-4 shrink-0"></span>
                                    تعطيل
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 p-3 sm:p-4 mb-4 rounded-10px border border-d9 bg-[#FAFAF4]">
            <div class="flex items-center gap-2 min-w-0">
                <span class="icon-[tabler--users] size-4 text-gray shrink-0"></span>
                <span class="font-bold text-13px text-primary">{{ (int) ($bundle['students_count'] ?? 0) }}</span>
                <span class="font-medium text-12px text-gray truncate">طلاب</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="icon-[tabler--book] size-4 text-gray shrink-0"></span>
                <span class="font-bold text-13px text-primary">{{ (int) ($bundle['courses_count'] ?? 0) }}</span>
                <span class="font-medium text-12px text-gray truncate">دورات</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="icon-[tabler--cash] size-4 text-gray shrink-0"></span>
                <span class="font-bold text-13px text-primary truncate">{{ handlePrice($bundle['sales_amount'] ?? 0) }}</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="icon-[tabler--clock] size-4 text-gray shrink-0"></span>
                <span class="font-bold text-13px text-primary">{{ $bundle['duration_label'] ?? '00:00' }}</span>
                <span class="font-medium text-12px text-gray truncate">ساعات</span>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 mt-auto pt-1">
            <div class="flex items-center gap-1.5 min-w-0">
                <span class="icon-[tabler--player-play] size-4 text-gray shrink-0"></span>
                <span class="font-medium text-13px text-gray truncate">{{ (int) ($bundle['courses_count'] ?? 0) }} دورات</span>
            </div>
            <p class="font-bold text-16px text-primary shrink-0">{{ $bundle['price_label'] ?? 'مجانية' }}</p>
        </div>
    </div>
</article>
