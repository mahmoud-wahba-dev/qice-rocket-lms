@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $slug = $demoSlug ?? 'demo'; @endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الاختبارات',
        'subtitle' => 'جميع الاختبارات والدرجات',
    ])
        @slot('actions')
            <a href="#"
                class="inline-flex items-center gap-2 rounded-12px border border-color2 px-5 h-12 font-semibold text-16px text-color2 hover:opacity-90 transition bg-white">
                عرض جميع الاختبارات
            </a>
            <a href="{{ route('panel.v1.instructor.quizzes.create') }}"
                class="inline-flex items-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                إضافة اختبار جديد
            </a>
        @endslot
    @endcomponent

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($quizStats ?? [] as $stat)
            <div class="rounded-14px bg-primary text-white px-5 py-5 center gap-4 min-h-[110px]">
                <span class="icon-[tabler--school] size-8 text-white shrink-0"></span>
                <div>
                    <p class="font-semibold text-28px sm:text-30px leading-none mb-1.5">{{ $stat['value'] }}</p>
                    <p class="font-semibold text-15px sm:text-16px text-white/90 leading-snug">{{ $stat['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pending review --}}
    <div>
        <h2 class="font-semibold text-24px text-primary mb-1">اختبارات بانتظار المراجعة</h2>
        <p class="font-medium text-16px text-gray mb-4">
            لديك اختبارات بانتظار المراجعة، يرجى فحصها لحساب درجات الطلاب.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($pendingQuizzes ?? [] as $item)
                <a href="{{ route('panel.v1.instructor.quiz-results.grade', ['resultId' => $item['result_id']]) }}"
                    class="rounded-14px border border-d9 bg-white p-5 shadow-sm cursor-pointer hover:border-primary/40 transition block">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="size-10 rounded-full bg-primary/10 center shrink-0 overflow-hidden">
                                <span class="font-bold text-14px text-primary">{{ mb_substr($item['name'], 0, 1) }}</span>
                            </span>
                            <span class="font-semibold text-16px text-primary truncate">{{ $item['name'] }}</span>
                        </div>
                        <span class="inline-flex shrink-0 rounded-full bg-[#FEF3C7] px-3 py-1 font-semibold text-12px text-[#B45309]">
                            {{ $item['status'] }}
                        </span>
                    </div>

                    <h3 class="font-semibold text-16px sm:text-17px text-primary leading-snug mb-1.5">
                        {{ $item['title'] }}
                    </h3>
                    <p class="font-medium text-14px text-gray mb-3">{{ $item['course'] }}</p>
                    <p class="font-medium text-13px text-gray">{{ $item['date'] }}</p>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Tabs + tables --}}
    <div>
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-5 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-tab="#q-panel-2" role="tab" aria-selected="true">نتائج الطلاب</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-tab="#q-panel-1" role="tab" aria-selected="false">جميع الاختبارات</button>
        </nav>

        <div id="q-panel-1" class="hidden space-y-4" role="tabpanel">
            <div class="bg-white border border-d9 p-4 sm:p-5 rounded-14px">
                <div class="flex flex-col lg:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                        <input type="search"
                            placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك..."
                            class="input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px">
                    </div>
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                        <span class="icon-[tabler--filter] size-4"></span>
                        فلتر
                    </button>
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-white hover:bg-fa transition">
                        <span class="icon-[tabler--file-spreadsheet] size-4"></span>
                        استخراج في الأكسل
                    </button>
                </div>
            </div>

            <div class="bg-white border border-d9 rounded-14px overflow-x-auto">
                <table class="table w-full text-15px">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-4 py-3.5 text-start font-semibold">العنوان والدورة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">عدد الأسئلة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">توقيت (دقيقة)</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الدرجة الكاملة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">درجة للنجاح</th>
                            <th class="px-4 py-3.5 text-start font-semibold">عدد الطلاب</th>
                            <th class="px-4 py-3.5 text-start font-semibold">حالة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">تاريخ الانشاء</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الاجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizRows ?? [] as $index => $row)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-4 py-4 min-w-48">
                                    <p class="font-semibold text-16px text-primary">{{ $row['title'] }}</p>
                                    <p class="font-medium text-13px text-gray">{{ $row['course'] }}</p>
                                </td>
                                <td class="px-4 py-4 font-semibold">{{ $row['questions'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['duration'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['fullGrade'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['passGrade'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['students'] }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-13px text-[#059669]">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['created_at'] }}</td>
                                <td class="px-4 py-4">
                                    <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                        <button type="button"
                                            class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                            aria-label="الاجراء" id="quiz-row-menu-{{ $index }}">
                                            <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                            role="menu" aria-labelledby="quiz-row-menu-{{ $index }}">
                                            <li>
                                                <a href="{{ route('panel.v1.instructor.quizzes.view', ['id' => $row['id']]) }}"
                                                    class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض الاختبار</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('panel.v1.instructor.quizzes.edit', ['id' => $row['id']]) }}"
                                                    class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">تعديل</a>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('panel.v1.instructor.quizzes.delete', ['id' => $row['id']]) }}"
                                                    onsubmit="return confirm('حذف الاختبار؟');">
                                                    @csrf
                                                    <button type="submit"
                                                        class="dropdown-item px-4 py-2.5 font-medium text-15px text-[#EF4444] w-full text-start">حذف</button>
                                                </form>
                                            </li>
                                            <li>
                                                <a href="{{ route('panel.v1.instructor.quizzes.view', ['id' => $row['id']]) }}"
                                                    class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض النتائج</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
                                                    class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض الدورة</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="q-panel-2" class="space-y-4" role="tabpanel">
            <div class="bg-white border border-d9 p-4 sm:p-5 rounded-14px">
                <div class="flex flex-col lg:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                        <input type="search"
                            placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك..."
                            class="input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px">
                    </div>
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                        <span class="icon-[tabler--filter] size-4"></span>
                        فلتر
                    </button>
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-white hover:bg-fa transition">
                        <span class="icon-[tabler--file-spreadsheet] size-4"></span>
                        استخراج في الأكسل
                    </button>
                </div>
            </div>

            <div class="bg-white border border-d9 rounded-14px overflow-x-auto">
                <table class="table w-full text-15px">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-4 py-3.5 text-start font-semibold">المتدرب</th>
                            <th class="px-4 py-3.5 text-start font-semibold">العنوان والدورة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الدرجة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">المحاولات</th>
                            <th class="px-4 py-3.5 text-start font-semibold">تاريخ المحاولة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الحالة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الاجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizStudentRows ?? [] as $index => $row)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="size-11 rounded-full bg-primary/10 center shrink-0">
                                            <span class="font-bold text-16px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                                        </span>
                                        <span class="font-semibold text-16px text-primary whitespace-nowrap">{{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 min-w-48">
                                    <p class="font-semibold text-16px text-primary">{{ $row['title'] }}</p>
                                    <p class="font-medium text-13px text-gray">{{ $row['course'] }}</p>
                                </td>
                                <td class="px-4 py-4 font-semibold">{{ $row['grade'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['attempts'] }}</td>
                                <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['attempted_at'] }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-13px text-[#059669]">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                        <button type="button"
                                            class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                            aria-label="الاجراء" id="quiz-student-menu-{{ $index }}">
                                            <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                            role="menu" aria-labelledby="quiz-student-menu-{{ $index }}">
                                            <li>
                                                <a href="#"
                                                    class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary"
                                                    aria-haspopup="dialog" aria-controls="instructor-quiz-review-modal"
                                                    data-overlay="#instructor-quiz-review-modal">عرض النتيجة</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('panel_v1.instructor.components.quiz-builder-modal')
@include('panel_v1.instructor.components.quiz-review-modal')
@endsection
