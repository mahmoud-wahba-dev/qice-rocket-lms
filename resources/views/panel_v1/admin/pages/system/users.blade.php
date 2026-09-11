@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'المستخدمين',
        'subtitle' => $pageSubtitle ?? '',
    ])
        @slot('actions')
            <button type="button"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                اضافة مستخدم جديد
            </button>
        @endslot
    @endcomponent

    <nav class="tabs tabs-bordered flex w-full overflow-x-auto border-b border-d9 mb-2" role="tablist">
        @foreach ($userTabs ?? [] as $i => $tab)
            <button type="button"
                class="tab {{ $i === 0 ? 'active' : '' }} justify-center whitespace-nowrap font-semibold text-15px sm:text-16px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#admin-users-{{ $i }}" role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                {{ $tab }}
            </button>
        @endforeach
    </nav>

    <div id="admin-users-0" role="tabpanel">
        <div class="border border-d9 rounded-14px bg-white p-4 sm:p-6 shadow-sm">
            @include('panel_v1.admin.components.filter-bar')

            <div class="overflow-x-auto">
                <table class="table w-full text-14px sm:text-15px">
                    <thead>
                        <tr class="border-b border-d9 bg-f9 text-gray">
                            <th class="px-3 py-3.5 text-start font-semibold">#</th>
                            <th class="px-3 py-3.5 text-start font-semibold">الاسم</th>
                            <th class="px-3 py-3.5 text-start font-semibold">الدور</th>
                            <th class="px-3 py-3.5 text-start font-semibold">رصيد الحساب</th>
                            <th class="px-3 py-3.5 text-start font-semibold">الدخل</th>
                            <th class="px-3 py-3.5 text-start font-semibold">المجموعة</th>
                            <th class="px-3 py-3.5 text-start font-semibold">تاريخ التسجيل</th>
                            <th class="px-3 py-3.5 text-start font-semibold">الحالة</th>
                            <th class="px-3 py-3.5 text-start font-semibold">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userRows ?? [] as $row)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-3 py-4 font-medium text-primary">{{ $row['id'] }}</td>
                                <td class="px-3 py-4 min-w-48">
                                    <div class="flex items-center gap-3">
                                        <span class="size-10 rounded-full bg-primary/10 center font-bold text-14px text-primary shrink-0">
                                            {{ mb_substr($row['name'], 0, 1) }}
                                        </span>
                                        <div class="min-w-0 text-start">
                                            <p class="font-semibold text-primary truncate">{{ $row['name'] }}</p>
                                            <p class="font-medium text-12px text-gray truncate">{{ $row['email'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 font-medium text-primary">{{ $row['role'] }}</td>
                                <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ $row['balance'] }}</td>
                                <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ $row['income'] }}</td>
                                <td class="px-3 py-4 font-medium text-primary">{{ $row['group'] }}</td>
                                <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ $row['registered_at'] }}</td>
                                <td class="px-3 py-4">
                                    <span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-12px">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                        <button type="button" class="dropdown-toggle size-9 rounded-10px border border-d9 center hover:bg-[#FAFAF4]"
                                            aria-label="إجراءات">
                                            <span class="icon-[tabler--dots] size-4 text-primary"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20">
                                            <li><a href="{{ route('panel.v1.admin.system.section', ['section' => 'users']) }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">عرض الملف</a></li>
                                            <li><a href="{{ route('panel.v1.admin.system.section', ['section' => 'users']) }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">تعديل</a></li>
                                            <li><button type="button" onclick="alert('قريباً — حذف المستخدم من لوحة الإدارة القديمة /admin/users')" class="dropdown-item px-4 py-2.5 font-medium text-14px text-red-500 w-full text-start">حذف</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('panel_v1.admin.components.pagination', ['pagination' => $pagination ?? []])
        </div>
    </div>

    @foreach ($userTabs ?? [] as $i => $tab)
        @continue($i === 0)
        <div id="admin-users-{{ $i }}" class="hidden" role="tabpanel">
            <div class="border border-d9 rounded-14px bg-white px-6 py-16 text-center">
                <p class="font-semibold text-18px text-primary mb-2">{{ $tab }}</p>
                <p class="font-medium text-14px text-gray">لا توجد نتائج لهذا التبويب حالياً.</p>
            </div>
        </div>
    @endforeach
</div>
@endsection
