@extends('panel_v1.instructor.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الشهادات',
        'subtitle' => 'متابعة الشهادات الصادرة لطلاب دوراتك واختباراتك.',
    ])
        @slot('actions')
            <a href="{{ $allCertificatesUrl ?? route('panel.v1.instructor.certificates.students') }}"
                class="inline-flex items-center gap-2 rounded-12px border border-color2 px-5 h-12 font-semibold text-16px text-color2 hover:opacity-90 transition bg-white">
                عرض جميع الشهادات
            </a>
        @endslot
    @endcomponent

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($certificateStats ?? [] as $stat)
            <div class="rounded-14px bg-primary text-white px-4 sm:px-5 py-5 flex flex-col items-center justify-center text-center min-h-[120px] gap-2">
                <span class="icon-[tabler--school] size-7 text-color2 shrink-0"></span>
                <p class="font-semibold text-28px sm:text-30px leading-none">{{ $stat['value'] }}</p>
                <p class="font-semibold text-13px sm:text-14px text-white/90 leading-snug">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="font-semibold text-24px text-primary">أحدث الشهادات الصادرة</h2>
            <a href="{{ $allCertificatesUrl ?? route('panel.v1.instructor.certificates.students') }}"
                class="font-semibold text-14px text-color2 hover:underline">عرض الكل</a>
        </div>
        @if (empty($recentCertificates))
            <div class="border border-d9 rounded-14px bg-white p-8 text-center">
                <span class="icon-[tabler--certificate] size-10 text-d9 mx-auto mb-3 block"></span>
                <p class="font-medium text-15px text-gray">لا توجد شهادات صادرة بعد</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($recentCertificates as $cert)
                    <article class="rounded-14px border border-d9 bg-white overflow-hidden shadow-sm">
                        <div class="aspect-[4/3] bg-[#F1F5F9] center">
                            <span class="icon-[tabler--certificate] size-10 text-d9"></span>
                        </div>
                        <div class="px-4 py-3 min-h-14">
                            @if (!empty($cert['title']))
                                <p class="font-semibold text-14px text-primary truncate">{{ $cert['title'] }}</p>
                                <p class="font-medium text-12px text-gray truncate">{{ $cert['student'] ?? '' }}</p>
                            @endif
                            @if (!empty($cert['download_url']))
                                <a href="{{ $cert['download_url'] }}"
                                    class="inline-flex items-center gap-1 mt-2 font-semibold text-12px text-color2 hover:underline">
                                    <span class="icon-[tabler--download] size-3.5"></span>
                                    تحميل
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-5 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#cert-panel-1" role="tab" aria-selected="true">شهادات الإتمام</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#cert-panel-2" role="tab" aria-selected="false">شهادات الاختبارات</button>
        </nav>

        <div id="cert-panel-1" role="tabpanel" class="bg-white border border-d9 rounded-14px overflow-x-auto">
            <table class="table w-full text-15px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="px-4 py-3.5 text-start font-semibold">العنوان والدورة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الشهادات المولدة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">آخر شهادة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($completionRows ?? [] as $index => $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4 min-w-48">
                                <p class="font-semibold text-16px text-primary">{{ $row['title'] }}</p>
                                <p class="font-medium text-13px text-gray">{{ $row['course'] }}</p>
                            </td>
                            <td class="px-4 py-4 font-semibold">{{ $row['generated'] }}</td>
                            <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['last_at'] }}</td>
                            <td class="px-4 py-4">
                                <a href="{{ $row['view_url'] ?? '#' }}"
                                    class="inline-flex items-center gap-1.5 rounded-10px border border-d9 px-3 h-10 font-semibold text-13px text-primary hover:bg-fa transition">
                                    عرض الشهادات
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center font-medium text-15px text-gray">
                                لا توجد دورات مفعّل لها شهادات إتمام بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cert-panel-2" class="hidden bg-white border border-d9 rounded-14px overflow-x-auto" role="tabpanel">
            <table class="table w-full text-15px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="px-4 py-3.5 text-start font-semibold">العنوان والدورة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الشهادات المولدة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">آخر شهادة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($examRows ?? [] as $index => $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4 min-w-48">
                                <p class="font-semibold text-16px text-primary">{{ $row['title'] }}</p>
                                <p class="font-medium text-13px text-gray">{{ $row['course'] }}</p>
                            </td>
                            <td class="px-4 py-4 font-semibold">{{ $row['generated'] }}</td>
                            <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['last_at'] }}</td>
                            <td class="px-4 py-4">
                                <a href="{{ $row['view_url'] ?? '#' }}"
                                    class="inline-flex items-center gap-1.5 rounded-10px border border-d9 px-3 h-10 font-semibold text-13px text-primary hover:bg-fa transition">
                                    عرض الشهادات
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center font-medium text-15px text-gray">
                                لا توجد اختبارات مفعّل لها شهادات بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
