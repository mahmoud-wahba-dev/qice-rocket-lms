@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title'    => 'تفاصيل الحضور',
        'subtitle' => ($session->title ?? 'جلسة #'.$session->id) . ' — ' . ($session->webinar->title ?? ''),
        'back'     => ['label' => 'العودة للحضور', 'url' => route('panel.v1.admin.education.section', ['section' => 'attendance'])],
    ])

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['label' => 'إجمالي المتدربين', 'value' => $totalAll,     'bg' => 'bg-[#EFF6FF]', 'color' => 'text-[#2563EB]', 'icon' => 'icon-[tabler--users]'],
            ['label' => 'حاضر',             'value' => $totalPresent,  'bg' => 'bg-[#D1FAE5]', 'color' => 'text-[#059669]', 'icon' => 'icon-[tabler--check]'],
            ['label' => 'متأخر',            'value' => $totalLate,     'bg' => 'bg-[#FEF3C7]', 'color' => 'text-[#D97706]', 'icon' => 'icon-[tabler--clock]'],
            ['label' => 'غائب',             'value' => $totalAbsent,   'bg' => 'bg-[#FEE2E2]', 'color' => 'text-[#DC2626]', 'icon' => 'icon-[tabler--x]'],
        ] as $st)
            <div class="border border-d9 rounded-14px bg-white p-4 flex items-center justify-between gap-3 shadow-sm">
                <div>
                    <p class="font-bold text-22px text-primary leading-none mb-1">{{ $st['value'] }}</p>
                    <p class="font-medium text-12px text-gray">{{ $st['label'] }}</p>
                </div>
                <span class="size-11 rounded-full center shrink-0 {{ $st['bg'] }}">
                    <span class="{{ $st['icon'] }} size-5 {{ $st['color'] }}"></span>
                </span>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ $searchQuery }}"
            placeholder="ابحث بالاسم..."
            class="input input-bordered h-10 rounded-12px border-d9 text-14px w-48">
        <select name="status" class="select select-bordered h-10 rounded-12px border-d9 text-14px">
            <option value="">كل الحالات</option>
            <option value="present" {{ $statusFilter === 'present' ? 'selected' : '' }}>حاضر</option>
            <option value="late"    {{ $statusFilter === 'late'    ? 'selected' : '' }}>متأخر</option>
            <option value="absent"  {{ $statusFilter === 'absent'  ? 'selected' : '' }}>غائب</option>
        </select>
        <button type="submit" class="h-10 px-5 rounded-12px bg-primary text-white font-bold text-13px">تصفية</button>
        @if ($searchQuery || $statusFilter)
            <a href="{{ route('panel.v1.admin.education.attendances.details', ['sessionId' => $session->id]) }}"
               class="h-10 px-5 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary center">
                إعادة ضبط
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="border border-d9 rounded-14px bg-white overflow-hidden shadow-sm">
        <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
            <h2 class="font-bold text-16px text-primary">المتدربون — {{ $students->total() }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-14px">
                <thead>
                    <tr class="bg-fa border-b border-d9 text-gray">
                        <th class="px-4 py-3 text-start">المتدرب</th>
                        <th class="px-4 py-3 text-center">وقت الانضمام</th>
                        <th class="px-4 py-3 text-center">الحالة</th>
                        <th class="px-4 py-3 text-center">تغيير الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $att = $student->attendance;
                            $currentStatus = $att ? $att->status : 'absent';
                            $isEdited = $att && !empty($att->edited_at);
                            $statusMap = [
                                'present' => ['label' => 'حاضر', 'bg' => 'bg-[#D1FAE5]', 'color' => 'text-[#059669]'],
                                'late'    => ['label' => 'متأخر', 'bg' => 'bg-[#FEF3C7]', 'color' => 'text-[#D97706]'],
                                'absent'  => ['label' => 'غائب',  'bg' => 'bg-[#FEE2E2]', 'color' => 'text-[#DC2626]'],
                            ];
                            $badge = $statusMap[$currentStatus] ?? $statusMap['absent'];
                        @endphp
                        <tr class="border-b border-d9 last:border-0 hover:bg-[#FAFAF4] transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-9 rounded-full bg-primary/10 center shrink-0 overflow-hidden">
                                        @if (!empty($student->avatar))
                                            <img src="{{ $student->getAvatar() }}" alt="{{ $student->full_name }}" class="size-full object-cover">
                                        @else
                                            <span class="icon-[tabler--user] size-5 text-primary"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-13px text-primary leading-snug">{{ $student->full_name }}</p>
                                        <p class="font-medium text-11px text-gray truncate">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($student->joined_at)
                                    <p class="font-medium text-13px text-primary whitespace-nowrap">{{ date('Y/m/d H:i', (int) $student->joined_at) }}</p>
                                    @if ($isEdited)
                                        <p class="font-medium text-11px text-[#D97706] mt-0.5">تم التعديل يدوياً</p>
                                    @endif
                                @else
                                    <span class="font-medium text-12px text-gray">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-1 font-semibold text-11px {{ $badge['bg'] }} {{ $badge['color'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @foreach (['present' => 'حاضر', 'late' => 'متأخر', 'absent' => 'غائب'] as $st => $stLabel)
                                        @if ($st !== $currentStatus)
                                            <form method="POST"
                                                  action="{{ route('panel.v1.admin.education.attendances.status', ['sessionId' => $session->id, 'studentId' => $student->id, 'status' => $st]) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="h-8 px-3 rounded-10px border font-semibold text-11px transition
                                                        {{ $st === 'present' ? 'border-[#059669] text-[#059669] hover:bg-[#D1FAE5]' : ($st === 'late' ? 'border-[#D97706] text-[#D97706] hover:bg-[#FEF3C7]' : 'border-[#DC2626] text-[#DC2626] hover:bg-[#FEE2E2]') }}">
                                                    {{ $stLabel }}
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center font-medium text-14px text-gray">
                                لا توجد نتائج تطابق الفلتر المحدد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-d9">
                {{ $students->withQueryString()->links('vendor.pagination.landing_v1') }}
            </div>
        @endif
    </div>
</div>
@endsection
