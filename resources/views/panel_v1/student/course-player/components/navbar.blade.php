@php
    $courseSlug = $slug ?? 'demo';
    $certLocked = $certificateLocked ?? true;
@endphp

<header class="sticky top-0 z-50 bg-primary text-white shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-8 h-[4.5rem]">
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <button type="button"
                class="lg:hidden btn btn-text btn-square text-white hover:bg-white/10"
                data-course-sidebar-toggle aria-label="قائمة المحتوى" aria-controls="course-player-sidebar"
                aria-expanded="false">
                <span class="icon-[tabler--menu-2] size-6"></span>
            </button>

            <a href="{{ route('panel.v1.student.home') }}" class="shrink-0 flex items-center gap-2">
                <img src="{{ asset('assets/landing_v1/logo_nav.svg') }}" alt="QIEC"
                    class="h-10 sm:h-12 brightness-0 invert" width="auto" height="48" decoding="async">
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 md:gap-6 lg:w-[76%] justify-between">
            @include('panel_v1.student.course-player.components.tools-dropdown')

            <button type="button"
                class="inline-flex items-center gap-1.5 sm:gap-2 rounded-10px border border-white/25 bg-white/5 px-2 sm:px-3 py-1.5 sm:py-2 font-semibold text-13px sm:text-14px text-white/90 cursor-default shrink-0"
                @if ($certLocked) aria-disabled="true" title="الشهادة مقفلة حتى إتمام الدورة" @endif>
                <span class="icon-[tabler--lock] size-4 opacity-80"></span>
                الشهادة
            </button>

            <div class="dropdown relative inline-flex rtl:[--placement:bottom-end]">
                <button id="course-player-user-toggle" type="button"
                    class="dropdown-toggle flex items-center gap-2 rounded-10px px-1.5 py-1.5 hover:bg-white/10 transition"
                    aria-haspopup="menu" aria-expanded="false" aria-label="قائمة المستخدم">
                    <div class="size-9 rounded-full overflow-hidden bg-white flex items-center justify-center shrink-0">
                        <img src="{{ auth()->user()->getAvatar() }}"
                             alt="{{ auth()->user()->full_name }}"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <span class="hidden w-full h-full items-center justify-center font-bold text-16px text-primary bg-white">
                            {{ mb_substr(auth()->user()->full_name ?? 'م', 0, 1) }}
                        </span>
                    </div>
                    <span class="max-md:hidden font-semibold text-14px text-white">{{ auth()->user()->full_name }}</span>
                    <span class="icon-[tabler--chevron-down] size-4 text-white/70 max-md:hidden dropdown-open:rotate-180 transition-transform duration-200"></span>
                </button>

                <div class="dropdown-menu dropdown-open:opacity-100 hidden w-72 p-3 rounded-16px border border-d9 shadow-[0_12px_40px_rgba(15,76,69,0.12)] bg-white"
                    role="menu" aria-orientation="vertical" aria-labelledby="course-player-user-toggle">
                    <ul class="py-1">
                        <li>
                            <a href="{{ route('panel.v1.student.home') }}"
                                class="dropdown-item rounded-10px px-4 py-3 font-semibold text-15px text-primary hover:bg-fa transition">
                                لوحة التعلم
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('panel.v1.student.purchases') }}"
                                class="dropdown-item rounded-10px px-4 py-3 font-semibold text-15px text-primary hover:bg-fa transition">
                                مشترياتي
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('panel.v1.student.settings') }}"
                                class="dropdown-item rounded-10px px-4 py-3 font-semibold text-15px text-primary hover:bg-fa transition">
                                الاعدادات
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('panel.v1.student.support') }}"
                                class="dropdown-item rounded-10px px-4 py-3 font-semibold text-15px text-primary hover:bg-fa transition">
                                الدعم
                            </a>
                        </li>
                        <li>
                            <a href="/logout"
                                class="dropdown-item rounded-10px px-4 py-3 font-semibold text-15px text-[#E11D48] hover:bg-red-50 transition">
                                تسجيل الخروج
                            </a>
                        </li>
                    </ul>
                    <div class="border-t border-d9 mt-2 pt-3">
                        <a href="{{ route('panel.v1.student.purchases') }}"
                            class="flex items-center gap-3 rounded-14px bg-primary px-4 py-3.5 text-white hover:bg-primary/95 transition">
                            <span class="size-11 rounded-full bg-white/10 center shrink-0">
                                <span class="icon-[tabler--rocket] size-6 text-[#0FC787]"></span>
                            </span>
                            <span class="flex flex-col gap-0.5 text-start">
                                <span class="font-bold text-14px leading-snug">الترقية إلى باقة PRO</span>
                                <span class="font-medium text-13px text-[#0FC787]">ترقية الآن</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
