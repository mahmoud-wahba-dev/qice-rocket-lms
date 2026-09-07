@extends('panel_v1.instructor.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الشهادات',
        'subtitle' => 'متابعة الشهادات الصادرة وتصميم القوالب المعتمدة للطلاب.',
    ])
        @slot('actions')
            <a href="#"
                class="inline-flex items-center gap-2 rounded-12px border border-color2 px-5 h-12 font-semibold text-16px text-color2 hover:opacity-90 transition bg-white">
                عرض جميع الشهادات
            </a>
            <a href="#"
                class="inline-flex items-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                تصميم قالب شهادة جديد
            </a>
        @endslot
    @endcomponent

    {{-- Stats — 4 primary cards, gold icon --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($certificateStats ?? [] as $stat)
            <div class="rounded-14px bg-primary text-white px-4 sm:px-5 py-5 flex flex-col items-center justify-center text-center min-h-[120px] gap-2">
                <span class="icon-[tabler--school] size-7 text-color2 shrink-0"></span>
                <p class="font-semibold text-28px sm:text-30px leading-none">{{ $stat['value'] }}</p>
                <p class="font-semibold text-13px sm:text-14px text-white/90 leading-snug">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Latest issued --}}
    <div>
        <h2 class="font-semibold text-24px text-primary mb-4">أحدث الشهادات الصادرة</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($recentCertificates ?? [] as $cert)
                <article class="rounded-14px border border-d9 bg-white overflow-hidden shadow-sm">
                    <div class="aspect-[4/3] bg-[#F1F5F9] center">
                        @if (!empty($cert['preview']))
                            <img src="{{ $cert['preview'] }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="icon-[tabler--certificate] size-10 text-d9"></span>
                        @endif
                    </div>
                    <div class="px-4 py-3 min-h-14">
                        @if (!empty($cert['title']))
                            <p class="font-semibold text-14px text-primary truncate">{{ $cert['title'] }}</p>
                            <p class="font-medium text-12px text-gray truncate">{{ $cert['student'] ?? '' }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    {{-- Tabs + table --}}
    <div>
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-5 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-tab="#cert-panel-1" role="tab" aria-selected="true">شهادات الإتمام</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-tab="#cert-panel-2" role="tab" aria-selected="false">شهادات الاختبارات</button>
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
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button"
                                        class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                        aria-label="الاجراء" id="cert-complete-menu-{{ $index }}">
                                        <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                        role="menu" aria-labelledby="cert-complete-menu-{{ $index }}">
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض الشهادات</a></li>
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">تعديل القالب</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center font-medium text-15px text-gray">
                                لا توجد شهادات إتمام بعد
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
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button"
                                        class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                        aria-label="الاجراء" id="cert-exam-menu-{{ $index }}">
                                        <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                        role="menu" aria-labelledby="cert-exam-menu-{{ $index }}">
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض الشهادات</a></li>
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">تعديل القالب</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center font-medium text-15px text-gray">
                                لا توجد شهادات اختبارات بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
