@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 text-start">
            <h1 class="font-semibold text-24px text-black mb-1">{{ $welcomeTitle }}</h1>
            <p class="font-medium text-16px text-gray max-w-2xl">{{ $welcomeSubtitle }}</p>
        </div>
        <a href="{{ route('panel.v1.admin.education.section', ['section' => 'courses']) }}"
            class="inline-flex items-center justify-center h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition shrink-0">
            + إنشاء دورة جديدة
        </a>
    </div>

    @include('panel_v1.admin.components.stats-cards', ['stats' => $stats ?? []])

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
        <div class="lg:col-span-7 xl:col-span-7 space-y-5 sm:space-y-6">
            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    <h2 class="font-bold text-18px sm:text-20px text-primary text-start">الرسم البياني للنشاط الأكاديمي</h2>
                    <p class="font-medium text-12px text-gray">آخر 7 أيام</p>
                </div>
                <div id="admin-academic-activity-chart"
                    class="min-h-[260px] w-full"
                    data-admin-apex-chart
                    data-chart='@json($activityChart ?? ['labels' => [], 'series' => []], JSON_UNESCAPED_UNICODE)'></div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                    @foreach ($chartMetrics ?? [] as $metric)
                        <div class="rounded-12px border border-d9 bg-[#FAFAF4] px-3 py-3 text-center">
                            <p class="font-bold text-18px text-primary mb-1">{{ $metric['value'] }}</p>
                            <p class="font-medium text-12px text-gray">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6 min-h-40">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-2 text-start">أحدث استفسارات منتديات الأسئلة</h2>
                <p class="font-medium text-14px text-gray text-start">لا توجد استفسارات حالياً.</p>
            </div>
        </div>

        <div class="lg:col-span-5 xl:col-span-5 space-y-5 sm:space-y-6">
            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-5 text-start">آخر الدورات</h2>
                <div class="space-y-3">
                    @forelse ($latestCourses ?? [] as $course)
                        <article class="flex items-center gap-3 rounded-12px border border-d9 bg-[#FAFAF4] p-3">
                            <div class="size-14 rounded-10px bg-primary/10 center shrink-0">
                                <span class="icon-[tabler--book] size-6 text-primary"></span>
                            </div>
                            <div class="min-w-0 flex-1 text-start">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <p class="font-semibold text-15px text-primary truncate">{{ $course['title'] }}</p>
                                    <span @class([
                                        'shrink-0 rounded-full px-2.5 py-0.5 font-semibold text-11px',
                                        'bg-[#D1FAE5] text-[#059669]' => ($course['statusTone'] ?? '') === 'success',
                                        'bg-[#FEF3C7] text-[#D97706]' => ($course['statusTone'] ?? '') === 'warning',
                                        'bg-[#EFF6FF] text-[#2563EB]' => ($course['statusTone'] ?? '') === 'info',
                                        'bg-[#FEE2E2] text-[#DC2626]' => ($course['statusTone'] ?? '') === 'danger',
                                    ])>
                                        {{ $course['status'] }}
                                    </span>
                                </div>
                                <p class="font-medium text-13px text-gray">{{ $course['type'] }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="font-medium text-14px text-gray text-start">لا توجد دورات بعد.</p>
                    @endforelse
                </div>
            </div>

            <div class="border border-d9 rounded-14px bg-white p-5 sm:p-6 min-h-40">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-2 text-start">الواجبات والتكليفات بانتظار التصحيح</h2>
                <p class="font-medium text-14px text-gray text-start">لا توجد تكليفات بانتظار التصحيح.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/design_1/vendor/apexcharts/apexcharts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;

    var el = document.querySelector('[data-admin-apex-chart]');
    if (!el) return;

    var payload = {};
    try {
        payload = JSON.parse(el.getAttribute('data-chart') || '{}');
    } catch (e) {
        payload = {};
    }

    var labels = payload.labels || [];
    var series = payload.series || [];

    var chart = new ApexCharts(el, {
        series: series,
        chart: {
            height: 280,
            type: 'area',
            fontFamily: 'Cairo, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false },
            dropShadow: {
                enabled: true,
                top: 10,
                left: 0,
                blur: 4,
                color: 'rgba(15, 76, 69, 0.25)',
                opacity: 0.25
            }
        },
        colors: ['#0F4C45', '#C99C69'],
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            curve: 'smooth',
            width: 3,
            lineCap: 'round'
        },
        fill: {
            type: 'gradient',
            gradient: {
                type: 'vertical',
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.28,
                opacityTo: 0.04,
                stops: [0, 90, 100]
            }
        },
        labels: labels,
        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'start',
            fontSize: '13px',
            fontWeight: 600,
            labels: { colors: '#8E8F8F' },
            markers: { width: 10, height: 10, radius: 10 }
        },
        grid: {
            borderColor: '#E8E8E8',
            strokeDashArray: 4,
            padding: { left: 8, right: 8 }
        },
        xaxis: {
            categories: labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { colors: '#8E8F8F', fontSize: '12px', fontFamily: 'Cairo, sans-serif' }
            }
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: { colors: '#8E8F8F', fontSize: '12px', fontFamily: 'Cairo, sans-serif' },
                formatter: function (val) {
                    return Math.round(val);
                }
            }
        },
        tooltip: {
            theme: 'light',
            style: { fontSize: '13px', fontFamily: 'Cairo, sans-serif' }
        }
    });

    chart.render();
});
</script>
@endpush
