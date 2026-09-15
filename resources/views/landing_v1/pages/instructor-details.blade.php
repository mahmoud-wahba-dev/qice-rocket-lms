@extends('landing_v1.layouts.app')

@section('content')
@php
    $rating = (float) ($rating ?? 0);
    $studentsCount = (int) ($studentsCount ?? 0);
    $coursesCount = (int) ($coursesCount ?? 0);
    $headline = trim((string) ($headline ?? ''));
    $aboutText = trim((string) ($aboutText ?? ''));
    $statCards = $statCards ?? [];
    $avatar = $instructor->getAvatar(200);
    $profileUrl = $profileUrl ?? url()->current();
    $hasAbout = $aboutText !== '';
@endphp

<main class="bg-[#F7F7F2] pb-16 lg:pb-24" data-instructor-profile>
    <section class="container pt-8 lg:pt-12">
        {{-- Hero --}}
        <div class="rounded-20px border border-[#00000014] bg-white p-5 sm:p-7 lg:p-8 mb-5 sm:mb-6 shadow-sm">
            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
                <div class="flex flex-col sm:flex-row gap-5 sm:gap-6 flex-1 min-w-0 w-full">
                    <div class="relative shrink-0 mx-auto sm:mx-0 flex flex-col items-center">
                        <div class="size-28 sm:size-32 lg:size-36 rounded-full overflow-hidden border-4 border-white shadow-md bg-primary/10">
                            <img src="{{ $avatar }}" alt="{{ $instructor->full_name }}"
                                class="w-full h-full object-cover">
                        </div>
                    </div>

                    <div class="min-w-0 flex-1 text-center sm:text-start pt-2 sm:pt-0">
                        <h1 class="font-bold text-26px sm:text-30px lg:text-34px text-primary mb-1.5 leading-snug">
                            {{ $instructor->full_name }}
                        </h1>
                        @if ($headline !== '')
                            <p class="font-medium text-15px sm:text-17px text-gray mb-4 sm:mb-5">{{ $headline }}</p>
                        @endif

                        <div class="flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-2.5">
                            @if ($rating > 0)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-[#FAFAF4] px-3 py-1.5 font-medium text-13px text-primary">
                                    <span class="icon-[tabler--star-filled] size-4 text-[#F59E0B]"></span>
                                    {{ number_format($rating, 1) }} تقييم الدورات
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-[#FAFAF4] px-3 py-1.5 font-medium text-13px text-primary">
                                <span class="icon-[tabler--users] size-4 text-primary"></span>
                                {{ number_format($studentsCount) }} طالب
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-[#FAFAF4] px-3 py-1.5 font-medium text-13px text-primary">
                                <span class="icon-[tabler--book-2] size-4 text-primary"></span>
                                {{ number_format($coursesCount) }} دورة تدريبية
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row lg:flex-col gap-2.5 w-full lg:w-48 shrink-0">
                    <button type="button"
                        class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 h-12 rounded-12px border border-primary text-primary font-semibold text-15px hover:bg-primary/5 transition"
                        data-share-profile
                        data-share-url="{{ $profileUrl }}"
                        data-share-title="{{ $instructor->full_name }}">
                        <span class="icon-[tabler--share] size-5"></span>
                        مشاركة
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats (real DB only) --}}
        @if (!empty($statCards))
            <div class="grid grid-cols-2 {{ count($statCards) >= 3 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-3 sm:gap-4 mb-6 sm:mb-8">
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
        @endif

        {{-- Tabs: only sections with real content --}}
        @php
            $tabs = [
                ['id' => 'about', 'label' => 'نبذة عن المدرب', 'icon' => 'icon-[tabler--user]', 'show' => $hasAbout],
                ['id' => 'courses', 'label' => 'الدورات التدريبية', 'icon' => 'icon-[tabler--school]', 'show' => true],
            ];
            $visibleTabs = array_values(array_filter($tabs, fn ($t) => !empty($t['show'])));
            $defaultTab = $visibleTabs[0]['id'] ?? 'courses';
        @endphp

        <nav class="flex w-full overflow-x-auto gap-2 sm:gap-2.5 mb-6 sm:mb-8 pb-1" role="tablist"
            aria-label="أقسام الملف الشخصي">
            @foreach ($visibleTabs as $i => $tab)
                <button type="button"
                    class="instructor-profile-tab shrink-0 inline-flex items-center gap-2 rounded-12px px-3.5 sm:px-4 py-2.5 sm:py-3 font-semibold text-13px sm:text-15px border transition
                        {{ $tab['id'] === $defaultTab
                            ? 'bg-primary text-white border-primary'
                            : 'bg-white text-primary border-d9 hover:border-primary/40' }}"
                    data-profile-tab="{{ $tab['id'] }}"
                    role="tab"
                    aria-selected="{{ $tab['id'] === $defaultTab ? 'true' : 'false' }}">
                    <span class="{{ $tab['icon'] }} size-5"></span>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        {{-- About --}}
        @if ($hasAbout)
            <div data-profile-panel="about" class="{{ $defaultTab === 'about' ? '' : 'hidden' }}" role="tabpanel">
                <div class="rounded-20px border border-[#00000014] bg-white p-5 sm:p-7 shadow-sm">
                    <h2 class="font-bold text-22px sm:text-24px text-primary mb-4 text-start">نبذة عني</h2>
                    <p class="font-medium text-15px sm:text-16px text-primary/80 leading-relaxed text-start whitespace-pre-line">
                        {{ $aboutText }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Courses --}}
        <div data-profile-panel="courses" class="{{ $defaultTab === 'courses' ? '' : 'hidden' }}" role="tabpanel">
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
    </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-instructor-profile]');
    if (!root) return;

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

    const shareBtn = root.querySelector('[data-share-profile]');
    if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
            const url = shareBtn.getAttribute('data-share-url') || window.location.href;
            const title = shareBtn.getAttribute('data-share-title') || document.title;
            try {
                if (navigator.share) {
                    await navigator.share({ title, url });
                    return;
                }
            } catch (e) {
                // user cancelled or share failed — fall through to clipboard
            }
            try {
                await navigator.clipboard.writeText(url);
                if (typeof window.showCartToast === 'function') {
                    window.showCartToast('تم', 'تم نسخ رابط الملف الشخصي', 'success');
                } else {
                    shareBtn.textContent = 'تم النسخ';
                    setTimeout(() => {
                        shareBtn.innerHTML = '<span class="icon-[tabler--share] size-5"></span> مشاركة';
                    }, 1500);
                }
            } catch (e) {
                window.prompt('انسخ الرابط:', url);
            }
        });
    }
});
</script>
@endpush
