@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $activeUsersTab = $activeUsersTab ?? 'all';
    $userTabs = $userTabs ?? [];
@endphp
<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'المستخدمين',
        'subtitle' => $pageSubtitle ?? '',
    ])
        @slot('actions')
            <div class="flex items-center gap-2">
                <a href="{{ route('panel.v1.admin.system.users.export', array_filter(['tab' => $activeUsersTab !== 'all' ? $activeUsersTab : null, 'search' => request('search')])) }}"
                    class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                    <span class="icon-[tabler--file-spreadsheet] size-4"></span>
                    تصدير Excel
                </a>
                <a href="{{ route('panel.v1.admin.system.users.create') }}"
                    class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition">
                    <span class="icon-[tabler--plus] size-5"></span>
                    اضافة مستخدم جديد
                </a>
            </div>
        @endslot
    @endcomponent

    <nav class="tabs tabs-bordered flex w-full overflow-x-auto border-b border-d9 mb-2" role="tablist">
        @foreach ($userTabs as $key => $tab)
            @php
                $tabUrl = route('panel.v1.admin.system.home', array_filter([
                    'tab' => $key !== 'all' ? $key : null,
                    'search' => request('search') ?: null,
                ]));
                $isActive = $activeUsersTab === $key;
            @endphp
            <a href="{{ $tabUrl }}"
                class="tab {{ $isActive ? 'active' : '' }} justify-center whitespace-nowrap font-semibold text-15px sm:text-16px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary gap-2"
                role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}">
                <span>{{ $tab['label'] ?? $key }}</span>
                <span class="inline-flex min-w-[1.5rem] h-6 px-1.5 rounded-full center font-semibold text-12px {{ $isActive ? 'bg-primary/10 text-primary' : 'bg-[#F3F4F6] text-gray' }}">
                    {{ number_format((int) ($tab['count'] ?? 0)) }}
                </span>
            </a>
        @endforeach
    </nav>

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
                    @forelse ($userRows ?? [] as $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-3 py-4 font-medium text-primary">{{ $row['id'] }}</td>
                            <td class="px-3 py-4 min-w-48">
                                <div class="flex items-center gap-3">
                                    @if (!empty($row['avatar']))
                                        <img src="{{ $row['avatar'] }}" alt=""
                                            class="size-10 rounded-full object-cover shrink-0 border border-d9 bg-primary/5">
                                    @else
                                        <span class="size-10 rounded-full bg-primary/10 center font-bold text-14px text-primary shrink-0">
                                            {{ mb_substr($row['name'] ?? '', 0, 1) }}
                                        </span>
                                    @endif
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
                                <span class="inline-flex rounded-full px-3 py-1 font-semibold text-12px {{ $row['status_class'] ?? 'bg-[#D1FAE5] text-[#059669]' }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                                <td class="px-3 py-4 text-center">
                                    @php
                                        $isAdminRow = !empty($row['is_admin']);
                                        $canImpersonate = !empty($row['can_impersonate']);
                                        $userMenuItems = [];
                                        if ($canImpersonate) {
                                            $userMenuItems[] = [
                                                'label' => 'تسجيل الدخول',
                                                'url' => route('panel.v1.admin.system.users.impersonate', ['id' => $row['id']]),
                                                'tone' => 'gray',
                                            ];
                                        }
                                        $userMenuItems[] = [
                                            'label' => 'تعديل',
                                            'url' => route('panel.v1.admin.system.users.edit', ['id' => $row['id']]),
                                            'tone' => 'gray',
                                            'disabled' => $isAdminRow,
                                        ];
                                        $userMenuItems[] = [
                                            'label' => 'حذف',
                                            'action' => route('panel.v1.admin.system.users.delete', ['id' => $row['id']]),
                                            'confirm' => 'حذف المستخدم؟',
                                            'tone' => 'danger',
                                            'disabled' => $isAdminRow,
                                        ];
                                    @endphp
                                    @include('panel_v1.components.actions-dropdown', [
                                        'id' => 'sys-user-'.$row['id'].'-'.$activeUsersTab,
                                        'items' => $userMenuItems,
                                    ])
                                </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <p class="font-semibold text-18px text-primary mb-2">لا يوجد مستخدمون</p>
                                <p class="font-medium text-14px text-gray">لا توجد نتائج لهذا التبويب حالياً.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator ?? null, 'pagination' => $pagination ?? []])
    </div>
</div>
@endsection
