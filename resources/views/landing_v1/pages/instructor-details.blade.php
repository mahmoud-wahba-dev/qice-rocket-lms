@extends('landing_v1.layouts.app')

@section('content')
@php
    $rating = is_array($instructor->rating ?? null)
        ? (float) ($instructor->rating['rate'] ?? 0)
        : (float) ($instructor->rating ?? 0);
    $studentsCount = (int) ($instructor->students_count ?? 0);
    $coursesCount = (int) ($instructor->courses_count ?? 0);
    $headline = $instructor->headline
        ?? ($profileMeta['headline'] ?? 'خبير جودة الرعاية الصحية والتحول الرقمي');
    $aboutText = trim(strip_tags($instructor->about ?? $instructor->bio ?? ''));
    if ($aboutText === '') {
        $aboutText = $profileMeta['about'] ?? '';
    }
    $achievements = $profileMeta['achievements'] ?? [];
    $certifications = $profileMeta['certifications'] ?? [];
    $statCards = $profileMeta['statCards'] ?? [];
    $consultation = $profileMeta['consultation'] ?? [];
    $badges = $profileMeta['badges'] ?? ['CBAHI', 'JCI', 'مدرب معتمد'];
    $avatar = $instructor->getAvatar(200);
@endphp

<main class="bg-[#F7F7F2] pb-16 lg:pb-24" data-instructor-profile>
    <section class="container pt-8 lg:pt-12">
        {{-- Hero --}}
        <div class="rounded-20px border border-[#00000014] bg-white p-5 sm:p-7 lg:p-8 mb-5 sm:mb-6 shadow-sm">
            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
                <div class="flex flex-col sm:flex-row gap-5 sm:gap-6 flex-1 min-w-0 w-full">
                    <div class="relative shrink-0 mx-auto sm:mx-0">
                        <div class="size-28 sm:size-32 lg:size-36 rounded-full overflow-hidden border-4 border-white shadow-md bg-primary/10">
                            <img src="{{ $avatar }}" alt="{{ $instructor->full_name }}"
                                class="w-full h-full object-cover">
                        </div>
                        <span
                            class="absolute -bottom-1 start-1/2 -translate-x-1/2 inline-flex items-center gap-1 rounded-full bg-primary px-2.5 py-1 font-semibold text-11px text-white whitespace-nowrap shadow">
                            <span class="icon-[tabler--rosette-discount-check] size-3.5"></span>
                            مدرب موثق
                        </span>
                    </div>

                    <div class="min-w-0 flex-1 text-center sm:text-start pt-2 sm:pt-0">
                        <h1 class="font-bold text-26px sm:text-30px lg:text-34px text-primary mb-1.5 leading-snug">
                            {{ $instructor->full_name }}
                        </h1>
                        <p class="font-medium text-15px sm:text-17px text-gray mb-4 sm:mb-5">{{ $headline }}</p>

                        <div class="flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-2.5 mb-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-[#FAFAF4] px-3 py-1.5 font-medium text-13px text-primary">
                                <span class="icon-[tabler--star-filled] size-4 text-[#F59E0B]"></span>
                                {{ $rating > 0 ? number_format($rating, 1) : '4.9' }} تقييم الدورات
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-[#FAFAF4] px-3 py-1.5 font-medium text-13px text-primary">
                                <span class="icon-[tabler--users] size-4 text-primary"></span>
                                +{{ number_format(max($studentsCount, 15000)) }} الطلاب
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-[#FAFAF4] px-3 py-1.5 font-medium text-13px text-primary">
                                <span class="icon-[tabler--book-2] size-4 text-primary"></span>
                                {{ max($coursesCount, 12) }} دورة تدريبية
                            </span>
                        </div>

                        <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                            @foreach ($badges as $badge)
                                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-3 py-1 font-semibold text-12px sm:text-13px text-primary">
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-row lg:flex-col gap-2.5 w-full lg:w-48 shrink-0">
                    <button type="button"
                        class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 h-12 rounded-12px bg-primary text-white font-semibold text-15px hover:opacity-90 transition">
                        <span class="icon-[tabler--user-plus] size-5"></span>
                        متابعة
                    </button>
                    <button type="button"
                        class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 h-12 rounded-12px border border-primary text-primary font-semibold text-15px hover:bg-primary/5 transition">
                        <span class="icon-[tabler--message-circle] size-5"></span>
                        تواصل مباشر
                    </button>
                    <button type="button"
                        class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 h-12 rounded-12px border border-primary text-primary font-semibold text-15px hover:bg-primary/5 transition">
                        <span class="icon-[tabler--share] size-5"></span>
                        مشاركة
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            @foreach ($statCards as $stat)
                <div class="rounded-16px border border-[#00000014] bg-white px-4 sm:px-5 py-4 sm:py-5 flex items-center justify-between gap-3 shadow-sm">
                    <div class="min-w-0 text-start">
                        <p class="font-bold text-22px sm:text-26px text-primary leading-none mb-1.5">{{ $stat['value'] }}</p>
                        <p class="font-medium text-13px sm:text-14px text-gray truncate">{{ $stat['label'] }}</p>
                    </div>
                    <span class="size-11 sm:size-12 rounded-full center shrink-0 {{ $stat['iconBg'] }}">
                        <span class="{{ $stat['icon'] }} size-5 sm:size-6 {{ $stat['iconColor'] }}"></span>
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Tabs --}}
        <nav class="flex w-full overflow-x-auto gap-2 sm:gap-2.5 mb-6 sm:mb-8 pb-1" role="tablist"
            aria-label="أقسام الملف الشخصي">
            @foreach ([
                ['id' => 'about', 'label' => 'نبذة عن المدرب', 'icon' => 'icon-[tabler--user]'],
                ['id' => 'courses', 'label' => 'الدورات التدريبية', 'icon' => 'icon-[tabler--school]'],
                ['id' => 'consult', 'label' => 'احجز جلسات استشارية', 'icon' => 'icon-[tabler--calendar-event]'],
                ['id' => 'articles', 'label' => 'المقالات والمنشورات', 'icon' => 'icon-[tabler--article]'],
                ['id' => 'reviews', 'label' => 'تقييمات الطلاب', 'icon' => 'icon-[tabler--message-2]'],
            ] as $i => $tab)
                <button type="button"
                    class="instructor-profile-tab shrink-0 inline-flex items-center gap-2 rounded-12px px-3.5 sm:px-4 py-2.5 sm:py-3 font-semibold text-13px sm:text-15px border transition
                        {{ $i === 0
                            ? 'bg-primary text-white border-primary'
                            : 'bg-white text-primary border-d9 hover:border-primary/40' }}"
                    data-profile-tab="{{ $tab['id'] }}"
                    role="tab"
                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                    <span class="{{ $tab['icon'] }} size-5"></span>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        {{-- About --}}
        <div data-profile-panel="about" role="tabpanel">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
                <div class="lg:col-span-8 space-y-6 rounded-20px border border-[#00000014] bg-white p-5 sm:p-7 shadow-sm">
                    <div>
                        <h2 class="font-bold text-22px sm:text-24px text-primary mb-4 text-start">نبذة عني</h2>
                        <p class="font-medium text-15px sm:text-16px text-primary/80 leading-relaxed text-start whitespace-pre-line">
                            {{ $aboutText }}
                        </p>
                    </div>
                    <div>
                        <h3 class="font-bold text-20px sm:text-22px text-primary mb-4 text-start">أبرز الإنجازات</h3>
                        <ul class="space-y-3">
                            @foreach ($achievements as $item)
                                <li class="flex items-start gap-3">
                                    <span class="size-6 rounded-full bg-[#0FC787]/15 center shrink-0 mt-0.5">
                                        <span class="icon-[tabler--check] size-3.5 text-[#0FC787]"></span>
                                    </span>
                                    <span class="font-medium text-15px sm:text-16px text-primary leading-relaxed text-start">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <aside class="lg:col-span-4 rounded-20px border border-[#00000014] bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="icon-[tabler--sparkles] size-5 text-[#C99C69]"></span>
                        <h3 class="font-bold text-18px sm:text-20px text-primary">الشهادات والاعتمادات</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach ($certifications as $cert)
                            <div class="flex items-center gap-3 rounded-14px bg-[#F5F5F0] border border-d9 px-3.5 py-3.5">
                                <span class="size-12 rounded-full center shrink-0 font-bold text-12px {{ $cert['badgeBg'] }} {{ $cert['badgeColor'] }}">
                                    {{ $cert['abbr'] }}
                                </span>
                                <div class="min-w-0 text-start">
                                    <p class="font-bold text-14px sm:text-15px text-primary truncate">{{ $cert['title'] }}</p>
                                    <p class="font-medium text-12px sm:text-13px text-gray truncate">{{ $cert['subtitle'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </div>
        </div>

        {{-- Courses --}}
        <div data-profile-panel="courses" class="hidden" role="tabpanel">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-6">
                @forelse ($courses as $course)
                    @if (($course->price ?? 0) > 0)
                        <x-landing_v1::course-card
                            :title="$course->title"
                            :description="$course->description"
                            :teacherName="$course->teacher->full_name ?? $instructor->full_name"
                            :teacherAvatar="!empty($course->teacher) ? $course->teacher->getAvatar() : $avatar"
                            :price="handlePrice($course->price)"
                            :image="$course->image_cover ?? $course->thumbnail ?? asset('assets/landing_v1/img/home/course.webp')"
                            :categoryTitle="$course->category->title ?? ''"
                            :slug="$course->slug"
                        />
                    @else
                        <x-landing_v1::workshop-card
                            :title="$course->title"
                            :summary="$course->summary ?? $course->description"
                            :categoryTitle="$course->category->title ?? ''"
                            :slug="$course->slug"
                        />
                    @endif
                @empty
                    <div class="col-span-full rounded-20px border border-[#00000014] bg-white px-6 py-16 text-center">
                        <p class="font-semibold text-18px text-primary mb-2">لا توجد دورات حالياً</p>
                        <p class="font-medium text-15px text-gray">سيظهر هنا محتوى الدورات التدريبية لهذا المدرب.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Consultations --}}
        <div data-profile-panel="consult" class="hidden" role="tabpanel">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
                @foreach ($consultation['chips'] ?? [] as $chip)
                    <div class="rounded-14px border border-primary/15 bg-primary/5 px-4 py-3.5 flex items-center gap-3">
                        <span class="size-10 rounded-full bg-white center shrink-0">
                            <span class="{{ $chip['icon'] }} size-5 text-primary"></span>
                        </span>
                        <div class="min-w-0 text-start">
                            <p class="font-medium text-12px text-gray mb-0.5">{{ $chip['label'] }}</p>
                            <p class="font-bold text-14px sm:text-15px text-primary truncate">{{ $chip['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
                <div class="rounded-20px border border-[#00000014] bg-white p-5 sm:p-6 shadow-sm" data-profile-calendar>
                    <div class="flex items-center justify-between mb-5">
                        <button type="button" class="size-9 rounded-10px border border-d9 center hover:bg-[#FAFAF4]" data-cal-prev aria-label="الشهر السابق">
                            <span class="icon-[tabler--chevron-right] size-5 text-primary"></span>
                        </button>
                        <h3 class="font-bold text-18px sm:text-20px text-primary" data-cal-title>أغسطس 2026</h3>
                        <button type="button" class="size-9 rounded-10px border border-d9 center hover:bg-[#FAFAF4]" data-cal-next aria-label="الشهر التالي">
                            <span class="icon-[tabler--chevron-left] size-5 text-primary"></span>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        @foreach (['أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت'] as $day)
                            <span class="text-center font-semibold text-12px text-gray py-2">{{ $day }}</span>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-1" data-cal-grid></div>
                </div>

                <div class="rounded-20px border border-[#00000014] bg-white p-5 sm:p-6 shadow-sm min-h-72 center flex-col text-center"
                    data-cal-slot>
                    <span class="size-16 rounded-16px bg-primary/10 center mb-4">
                        <span class="icon-[tabler--calendar-plus] size-8 text-primary"></span>
                    </span>
                    <p class="font-semibold text-16px sm:text-18px text-primary" data-cal-slot-text>
                        اختر تاريخاً من التقويم لحجز اجتماع
                    </p>
                </div>
            </div>
        </div>

        {{-- Articles placeholder --}}
        <div data-profile-panel="articles" class="hidden" role="tabpanel">
            <div class="rounded-20px border border-[#00000014] bg-white px-6 py-16 text-center shadow-sm">
                <span class="size-14 rounded-14px bg-primary/10 center mx-auto mb-4">
                    <span class="icon-[tabler--article] size-7 text-primary"></span>
                </span>
                <p class="font-semibold text-18px text-primary mb-2">المقالات والمنشورات</p>
                <p class="font-medium text-15px text-gray">سيُعرض هنا محتوى المقالات والبحوث قريباً.</p>
            </div>
        </div>

        {{-- Reviews placeholder --}}
        <div data-profile-panel="reviews" class="hidden" role="tabpanel">
            <div class="rounded-20px border border-[#00000014] bg-white px-6 py-16 text-center shadow-sm">
                <span class="size-14 rounded-14px bg-primary/10 center mx-auto mb-4">
                    <span class="icon-[tabler--message-2] size-7 text-primary"></span>
                </span>
                <p class="font-semibold text-18px text-primary mb-2">تقييمات الطلاب</p>
                <p class="font-medium text-15px text-gray">سيُعرض هنا تقييمات ومراجعات الطلاب قريباً.</p>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-instructor-profile]');
    if (!root) return;

    // Tabs
    const tabs = root.querySelectorAll('[data-profile-tab]');
    const panels = root.querySelectorAll('[data-profile-panel]');
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const id = tab.getAttribute('data-profile-tab');
            tabs.forEach((t) => {
                const on = t === tab;
                t.classList.toggle('bg-primary', on);
                t.classList.toggle('text-white', on);
                t.classList.toggle('border-primary', on);
                t.classList.toggle('bg-white', !on);
                t.classList.toggle('text-primary', !on);
                t.classList.toggle('border-d9', !on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach((p) => {
                p.classList.toggle('hidden', p.getAttribute('data-profile-panel') !== id);
            });
        });
    });

    // Calendar
    const cal = root.querySelector('[data-profile-calendar]');
    if (!cal) return;
    const grid = cal.querySelector('[data-cal-grid]');
    const title = cal.querySelector('[data-cal-title]');
    const slotText = root.querySelector('[data-cal-slot-text]');
    let view = new Date(2026, 7, 1); // Aug 2026
    let selected = null;
    const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

    const render = () => {
        const y = view.getFullYear();
        const m = view.getMonth();
        title.textContent = `${months[m]} ${y}`;
        grid.innerHTML = '';
        const firstDow = new Date(y, m, 1).getDay();
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        for (let i = 0; i < firstDow; i++) {
            grid.appendChild(document.createElement('span'));
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(d);
            const isSel = selected && selected.y === y && selected.m === m && selected.d === d;
            btn.className = `aspect-square rounded-10px font-semibold text-14px transition ${
                isSel ? 'bg-primary text-white' : 'text-primary hover:bg-primary/10'
            }`;
            btn.addEventListener('click', () => {
                selected = { y, m, d };
                if (slotText) {
                    slotText.textContent = `تم اختيار ${d} ${months[m]} ${y} — اختر وقت الجلسة قريباً`;
                }
                render();
            });
            grid.appendChild(btn);
        }
    };

    cal.querySelector('[data-cal-prev]')?.addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() - 1, 1);
        render();
    });
    cal.querySelector('[data-cal-next]')?.addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() + 1, 1);
        render();
    });
    render();
});
</script>
@endpush
