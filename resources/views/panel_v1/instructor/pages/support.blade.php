@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $landingImg = $landingImg ?? asset('assets/landing_v1/img');
@endphp

<div class="space-y-8 sm:space-y-10 pb-10" data-instructor-support>
    <div class="min-w-0">
        <h1 class="font-semibold text-28px sm:text-32px text-primary mb-2">مركز الدعم الفني وإدارة التذاكر</h1>
        <p class="font-medium text-16px sm:text-18px text-gray leading-relaxed max-w-4xl">
            متابعة التذاكر الحالية، التواصل المباشر مع فريق الدعم، وطلب المساعدة للفصول المباشرة في مكان واحد.
        </p>
    </div>

    {{-- Action cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
        <button type="button"
            class="rounded-14px bg-[#EAF6F2] border border-transparent px-5 py-7 sm:py-8 center flex-col text-center hover:border-primary/20 hover:shadow-sm transition"
            data-support-view="create">
            <span class="size-12 rounded-12px bg-primary center mb-4 shrink-0">
                <span class="icon-[tabler--ticket] size-6 text-white"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug mb-2">إنشاء تذكرة دعم جديدة</h2>
            <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed">فتح طلب جديد وتوجيهه للفريق المختص</p>
        </button>

        <button type="button"
            class="rounded-14px bg-[#EAF6F2] border border-transparent px-5 py-7 sm:py-8 center flex-col text-center hover:border-primary/20 hover:shadow-sm transition"
            data-support-view="tickets">
            <span class="size-12 rounded-12px bg-primary center mb-4 shrink-0">
                <span class="icon-[tabler--inbox] size-6 text-white"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug mb-2">عرض تذاكر الدعم</h2>
            <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed">متابعة حالة الطلبات والتذاكر الحالية</p>
        </button>

        <button type="button"
            class="rounded-14px bg-[#EAF6F2] border border-transparent px-5 py-7 sm:py-8 center flex-col text-center hover:border-primary/20 hover:shadow-sm transition"
            data-support-view="courses">
            <span class="size-12 rounded-12px bg-primary center mb-4 shrink-0">
                <span class="icon-[tabler--message-circle] size-6 text-white"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug mb-2">رسائل دعم الدورات</h2>
            <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed">استفسارات الطلاب الخاصة بالمنهج والدروس</p>
        </button>
    </div>

    {{-- Create ticket (reused student support form layout) --}}
    <div data-support-panel="create" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 overflow-hidden rounded-20px border border-d9 bg-white shadow-[0_8px_30px_rgba(15,76,69,0.08)]">
            <div class="lg:col-span-8 px-6 sm:px-8 py-8 lg:px-12 lg:py-12 order-2 lg:order-1">
                <h2 class="font-bold text-24px sm:text-32px text-primary mb-8 sm:mb-10 text-start">رسالة دعم جديدة</h2>

                <form class="space-y-7 sm:space-y-8" action="#" method="POST" onsubmit="return false;">
                    <div class="relative">
                        <label for="instructor-support-type"
                            class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                            اختر نوع الدعم
                        </label>
                        <select id="instructor-support-type"
                            class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                            <option selected disabled>النوع</option>
                            <option>دعم فني</option>
                            <option>دعم مالي</option>
                            <option>استفسار عام</option>
                        </select>
                    </div>

                    <div class="relative">
                        <label for="instructor-support-subject"
                            class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                            عنوان الموضوع
                        </label>
                        <input id="instructor-support-subject" type="text"
                            class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary"
                            placeholder="">
                    </div>

                    <div class="relative">
                        <label for="instructor-support-message"
                            class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                            الرسالة
                        </label>
                        <textarea id="instructor-support-message" rows="8"
                            class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-48 resize-y"
                            placeholder=""></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-10px h-14 w-full font-bold text-18px">
                        ارسل الرسالة
                    </button>
                </form>
            </div>

            <aside
                class="lg:col-span-4 bg-primary text-white px-8 sm:px-10 py-12 sm:py-14 flex flex-col items-center justify-center text-center gap-8 sm:gap-10 relative overflow-hidden order-1 lg:order-2">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -top-20 -start-16 size-56 rounded-full bg-primary blur-[97px]"></div>
                    <div class="absolute -bottom-20 -end-16 size-56 rounded-full bg-primary blur-[97px]"></div>
                </div>

                <div class="relative w-36 h-36 sm:w-40 sm:h-40">
                    <img src="{{ $landingImg }}/logo-footer.webp" alt="QIEC Training"
                        class="size-full object-contain brightness-0 invert" loading="lazy" decoding="async">
                </div>

                <div class="relative flex items-center justify-center gap-3">
                    <a href="#" aria-label="WhatsApp"
                        class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                        <span class="icon-[tabler--brand-whatsapp] size-7"></span>
                    </a>
                    <a href="#" aria-label="Telegram"
                        class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                        <span class="icon-[tabler--brand-telegram] size-7"></span>
                    </a>
                    <a href="#" aria-label="Instagram"
                        class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                        <span class="icon-[tabler--brand-instagram] size-7"></span>
                    </a>
                    <a href="#" aria-label="LinkedIn"
                        class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                        <span class="icon-[tabler--brand-linkedin] size-7"></span>
                    </a>
                </div>

                <p class="relative font-bold text-18px sm:text-22px text-white leading-relaxed max-w-xs">
                    نسعد جداً بتواصلكم معنا سوف يتم الرد عليكم قريباً ...
                </p>
            </aside>
        </div>
    </div>

    {{-- Tickets + course support --}}
    <div data-support-panel="list">
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-6 sm:mb-8 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-support-tab="tickets" role="tab" aria-selected="true">تذاكري</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-support-tab="courses" role="tab" aria-selected="false">دعم الدورات</button>
        </nav>

        <div data-support-tab-panel="tickets">
            <h2 class="font-semibold text-22px sm:text-24px text-primary mb-5 text-start">قائمة التذاكر الخاصة بك</h2>

            <div class="space-y-3">
                {{-- Header row --}}
                <div class="hidden lg:grid grid-cols-12 gap-3 px-5 py-2 font-semibold text-14px text-gray">
                    <div class="col-span-2">الرقم</div>
                    <div class="col-span-4">الموضوع</div>
                    <div class="col-span-2">التاريخ</div>
                    <div class="col-span-2">الحالة</div>
                    <div class="col-span-2 text-end">الاجراء</div>
                </div>

                @forelse ($supportTickets ?? [] as $index => $ticket)
                    <article class="rounded-14px border border-d9 bg-white px-4 sm:px-5 py-4 sm:py-5 shadow-sm">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 lg:gap-4 lg:items-center">
                            <div class="lg:col-span-2">
                                <p class="lg:hidden font-medium text-12px text-gray mb-1">الرقم</p>
                                <p class="font-bold text-16px text-primary">{{ $ticket['id'] }}</p>
                            </div>
                            <div class="lg:col-span-4 min-w-0">
                                <p class="lg:hidden font-medium text-12px text-gray mb-1">الموضوع</p>
                                <p class="font-semibold text-15px sm:text-16px text-primary leading-snug">{{ $ticket['subject'] }}</p>
                            </div>
                            <div class="lg:col-span-2">
                                <p class="lg:hidden font-medium text-12px text-gray mb-1">التاريخ</p>
                                <p class="font-medium text-14px sm:text-15px text-gray">{{ $ticket['date'] }}</p>
                            </div>
                            <div class="lg:col-span-2">
                                <p class="lg:hidden font-medium text-12px text-gray mb-1">الحالة</p>
                                <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1.5 font-semibold text-13px text-[#059669]">
                                    {{ $ticket['status'] }}
                                </span>
                            </div>
                            <div class="lg:col-span-2 flex items-center justify-start lg:justify-end gap-2">
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button"
                                        class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                        aria-label="المزيد" id="support-ticket-menu-{{ $index }}">
                                        <span class="icon-[tabler--dots] size-5 text-gray"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                        role="menu" aria-labelledby="support-ticket-menu-{{ $index }}">
                                        <li><a href="{{ url('/panel/support/' . $ticket['raw_id'] . '/conversations') }}" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض والرد</a></li>
                                    </ul>
                                </div>
                                <a href="{{ url('/panel/support/' . $ticket['raw_id'] . '/conversations') }}" class="font-semibold text-15px text-primary hover:opacity-80 transition">عرض والرد</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-14px border border-d9 bg-white px-6 py-16 text-center">
                        <p class="font-medium text-16px text-gray">لا توجد تذاكر حالياً</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div data-support-tab-panel="courses" class="hidden">
            <h2 class="font-semibold text-22px sm:text-24px text-primary mb-5 text-start">
                الدعم الفني الفوري للبث والجلسات المباشرة
            </h2>

            <div class="bg-white border border-d9 rounded-14px overflow-x-auto shadow-sm min-h-64">
                <table class="table w-full text-15px sm:text-16px">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-5 py-4 text-start font-semibold">الطالب</th>
                            <th class="px-5 py-4 text-start font-semibold">الدورة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courseSupportRows ?? [] as $row)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-5 py-5 font-semibold text-primary">{{ $row['student'] }}</td>
                                <td class="px-5 py-5 font-medium text-gray">{{ $row['course'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-20 text-center font-medium text-16px text-gray">
                                    لا توجد رسائل دعم دورات حالياً
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
