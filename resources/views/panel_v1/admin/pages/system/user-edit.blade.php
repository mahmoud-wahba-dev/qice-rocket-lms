@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $editUser = $editUser ?? null;
    $roles = $roles ?? collect();
    $courses = $courses ?? [];
    $manualAddedClasses = $manualAddedClasses ?? collect();
    $purchasedClasses = $purchasedClasses ?? collect();
    $manualDisabledClasses = $manualDisabledClasses ?? collect();
    $userLanguages = $userLanguages ?? [];
    $timezones = $timezones ?? [];
    $organizations = $organizations ?? [];
    $authAdmin = auth()->user();
    $isSelf = $authAdmin && (int) $authAdmin->id === (int) $editUser->id;
    $certificateAdditional = $editUser->certificate_additional
        ?? optional($editUser->userMetas->firstWhere('name', 'certificate_additional'))->value
        ?? '';
    $listUrl = route('panel.v1.admin.system.section', ['section' => 'users']);
@endphp

<div class="space-y-6 pb-10">
    @component('panel_v1.admin.components.page-header', [
        'title' => 'تعديل مستخدم',
        'subtitle' => ($editUser->full_name ?? '') . ' (#' . $editUser->id . ')',
    ])
        @slot('actions')
            <a href="{{ $listUrl }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                العودة للقائمة
            </a>
            @unless ($isSelf)
                <form method="POST" action="{{ route('panel.v1.admin.system.users.delete', ['id' => $editUser->id]) }}"
                    onsubmit='return confirm(@json("حذف هذا المستخدم نهائيًا؟ لا يمكن التراجع."));'>
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-[#DC2626] text-white font-semibold text-14px hover:opacity-95 transition">
                        <span class="icon-[tabler--trash] size-4"></span>
                        حذف المستخدم
                    </button>
                </form>
            @endunless
        @endslot
    @endcomponent

    @if ($errors->any())
        <div class="rounded-12px bg-[#FEF2F2] border border-[#FECACA] px-4 py-3">
            <ul class="list-disc list-inside space-y-1 font-medium text-14px text-[#DC2626]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- General form --}}
    <form method="POST" action="{{ $formAction }}" class="border border-d9 rounded-14px bg-white p-6 sm:p-8 space-y-5">
        @csrf
        <h2 class="font-bold text-18px text-primary">البيانات العامة</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="font-semibold text-14px text-primary mb-2 block">الاسم الكامل *</label>
                <input type="text" name="full_name" value="{{ old('full_name', $editUser->full_name) }}" required
                    class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none">
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">اسم المستخدم *</label>
                <input type="text" name="username" value="{{ old('username', $editUser->username) }}" required
                    class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" dir="ltr">
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">الدور *</label>
                <select name="role_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((int) old('role_id', $editUser->role_id) === (int) $role->id)>
                            {{ $role->caption ?? $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">البريد</label>
                <input type="email" name="email" value="{{ old('email', $editUser->email) }}"
                    class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" dir="ltr">
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">الجوال</label>
                <input type="text" name="mobile" value="{{ old('mobile', $editUser->mobile) }}"
                    class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" dir="ltr">
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">كلمة مرور جديدة</label>
                <input type="password" name="password"
                    class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"
                    placeholder="اتركها فارغة للإبقاء على الحالية">
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">الحالة *</label>
                <select name="status" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                    @foreach (\App\User::$statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $editUser->status) === $status)>
                            {{ $status === 'active' ? 'نشط' : ($status === 'pending' ? 'قيد المراجعة' : 'غير نشط') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">المنطقة الزمنية</label>
                <select name="timezone" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                    <option value="">— اختر —</option>
                    @foreach ($timezones as $tz)
                        <option value="{{ $tz }}" @selected(old('timezone', $editUser->timezone) === $tz)>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">اللغة</label>
                <select name="language" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                    <option value="">— اختر —</option>
                    @foreach ($userLanguages as $lang => $label)
                        <option value="{{ $lang }}" @selected(mb_strtolower((string) old('language', $editUser->language)) === mb_strtolower((string) $lang))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if (!empty($organizations))
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">المنظمة</label>
                    <select name="organ_id" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="">— بدون —</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org['id'] }}" @selected((int) old('organ_id', $editUser->organ_id) === (int) $org['id'])>
                                {{ $org['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="sm:col-span-2">
                <label class="font-semibold text-14px text-primary mb-2 block">نبذة قصيرة (Bio)</label>
                <textarea name="bio" rows="2" maxlength="48"
                    class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('bio', $editUser->bio) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="font-semibold text-14px text-primary mb-2 block">نبذة عن المستخدم</label>
                <textarea name="about" rows="4"
                    class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('about', $editUser->about) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="font-semibold text-14px text-primary mb-2 block">بيانات إضافية للشهادة</label>
                <input type="text" name="certificate_additional" value="{{ old('certificate_additional', $certificateAdditional) }}"
                    class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
            </div>
        </div>

        @php
            $flagOn = function (string $key, $default) {
                $old = old($key, null);
                if ($old !== null) {
                    return (string) $old === '1';
                }
                return (int) $default === 1 || $default === true;
            };
            $userFlags = [
                ['key' => 'ban', 'label' => 'حظر المستخدم', 'on' => $flagOn('ban', $editUser->ban), 'toggle' => 'data-ban-toggle'],
                ['key' => 'verified', 'label' => 'الشارة الزرقاء', 'on' => $flagOn('verified', $editUser->verified), 'toggle' => ''],
                ['key' => 'affiliate', 'label' => 'التسويق بالعمولة', 'on' => $flagOn('affiliate', $editUser->affiliate), 'toggle' => ''],
                ['key' => 'can_create_store', 'label' => 'إنشاء متجر', 'on' => $flagOn('can_create_store', $editUser->can_create_store), 'toggle' => ''],
                ['key' => 'access_content', 'label' => 'الوصول للمحتوى', 'on' => $flagOn('access_content', $editUser->access_content ?? 1), 'toggle' => ''],
                ['key' => 'enable_ai_content', 'label' => 'محتوى AI', 'on' => $flagOn('enable_ai_content', $editUser->enable_ai_content), 'toggle' => ''],
            ];
        @endphp

        <style>
            .user-flag-row { display:flex; align-items:center; justify-content:space-between; gap:12px; font-weight:500; font-size:13px; color:#0F3D36; border:1px solid #D9D9D9; border-radius:10px; padding:12px; cursor:pointer; user-select:none; background:#fff; }
            .user-flag-row:hover { background:#FAFAF4; }
            .user-flag-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
            .user-flag-switch input { position:absolute; inset:0; width:100%; height:100%; opacity:0; margin:0; cursor:pointer; z-index:2; }
            .user-flag-track { display:block; width:44px; height:24px; border-radius:999px; background:#D1D5DB; transition:background .15s ease; position:relative; pointer-events:none; }
            .user-flag-track::after { content:""; position:absolute; top:2px; right:2px; width:20px; height:20px; border-radius:999px; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.2); transition:transform .15s ease; }
            .user-flag-switch input:checked + .user-flag-track { background:#0F3D36; }
            .user-flag-switch input:checked + .user-flag-track::after { transform:translateX(-20px); }
            .user-flag-switch input:focus-visible + .user-flag-track { outline:2px solid #0F3D36; outline-offset:2px; }
        </style>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
            @foreach ($userFlags as $flag)
                <label class="user-flag-row">
                    <span>{{ $flag['label'] }}</span>
                    <span class="user-flag-switch">
                        <input type="hidden" name="{{ $flag['key'] }}" value="0">
                        <input type="checkbox"
                            name="{{ $flag['key'] }}"
                            id="user-flag-{{ $flag['key'] }}"
                            value="1"
                            @checked($flag['on'])
                            {{ $flag['toggle'] }}>
                        <span class="user-flag-track" aria-hidden="true"></span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 {{ $flagOn('ban', $editUser->ban) ? '' : 'hidden' }}" data-ban-dates>
            <div>
                <label class="font-semibold text-13px text-primary mb-1 block">بداية الحظر</label>
                <input type="date" name="ban_start_at"
                    value="{{ old('ban_start_at', $editUser->ban_start_at ? date('Y-m-d', (int) $editUser->ban_start_at) : '') }}"
                    class="input input-bordered w-full h-11 rounded-10px border-d9 text-13px">
            </div>
            <div>
                <label class="font-semibold text-13px text-primary mb-1 block">نهاية الحظر</label>
                <input type="date" name="ban_end_at"
                    value="{{ old('ban_end_at', $editUser->ban_end_at ? date('Y-m-d', (int) $editUser->ban_end_at) : '') }}"
                    class="input input-bordered w-full h-11 rounded-10px border-d9 text-13px">
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-d9">
            <button type="submit"
                class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                حفظ التعديلات
            </button>
            <a href="{{ $listUrl }}"
                class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                إلغاء
            </a>
        </div>
    </form>

    {{-- Assign course --}}
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8 space-y-5">
        <div>
            <h2 class="font-bold text-18px text-primary mb-1">إسناد دورة للمستخدم</h2>
            <p class="font-medium text-13px text-gray">أضف أي دورة يدويًا — تظهر فورًا في مشتريات المستخدم.</p>
        </div>

        <form method="POST" action="{{ route('panel.v1.admin.system.users.assign-course', ['id' => $editUser->id]) }}"
            class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
            @csrf
            <div class="flex-1 min-w-0">
                <label class="font-semibold text-14px text-primary mb-2 block">اختر الدورة *</label>
                <select name="webinar_id" required
                    class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                    <option value="">— اختر دورة —</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course['id'] }}">{{ $course['title'] }} (#{{ $course['id'] }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 h-12 px-6 rounded-12px bg-color2 text-white font-bold text-14px hover:opacity-95 transition shrink-0">
                <span class="icon-[tabler--plus] size-4"></span>
                إسناد الدورة
            </button>
        </form>

        <div>
            <h3 class="font-bold text-15px text-primary mb-3">الدورات المضافة يدويًا ({{ $manualAddedClasses->count() }})</h3>
            <div class="overflow-x-auto border border-d9 rounded-12px">
                <table class="table w-full text-13px">
                    <thead>
                        <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                            <th class="px-3 py-3 text-start">الدورة</th>
                            <th class="px-3 py-3 text-start">النوع</th>
                            <th class="px-3 py-3 text-start">المدرب</th>
                            <th class="px-3 py-3 text-start">تاريخ الإضافة</th>
                            <th class="px-3 py-3 text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($manualAddedClasses as $sale)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-3 py-3 font-semibold text-primary">{{ $sale->webinar->title ?? '—' }}</td>
                                <td class="px-3 py-3 text-gray">{{ $sale->webinar->type ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $sale->webinar->teacher->full_name ?? ($sale->webinar->creator->full_name ?? '—') }}</td>
                                <td class="px-3 py-3">{{ date('Y/m/d H:i', (int) $sale->created_at) }}</td>
                                <td class="px-3 py-3 text-center">
                                    <form method="POST" action="{{ route('panel.v1.admin.system.users.block-course', ['id' => $editUser->id, 'saleId' => $sale->id]) }}"
                                        onsubmit='return confirm(@json("إزالة وصول المستخدم لهذه الدورة؟"));' class="inline">
                                        @csrf
                                        <button type="submit" class="font-semibold text-13px text-[#DC2626] hover:underline">إزالة الوصول</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-8 text-center text-gray">لا توجد دورات مضافة يدويًا</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($purchasedClasses->isNotEmpty())
            <div>
                <h3 class="font-bold text-15px text-primary mb-3">مشتريات فعلية ({{ $purchasedClasses->count() }})</h3>
                <div class="overflow-x-auto border border-d9 rounded-12px">
                    <table class="table w-full text-13px">
                        <thead>
                            <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                                <th class="px-3 py-3 text-start">الدورة</th>
                                <th class="px-3 py-3 text-start">السعر</th>
                                <th class="px-3 py-3 text-start">تاريخ الشراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchasedClasses as $sale)
                                <tr class="border-b border-d9 last:border-0">
                                    <td class="px-3 py-3 font-semibold text-primary">{{ $sale->webinar->title ?? '—' }}</td>
                                    <td class="px-3 py-3">{{ handlePrice($sale->total_amount ?? $sale->amount ?? 0) }}</td>
                                    <td class="px-3 py-3">{{ date('Y/m/d H:i', (int) $sale->created_at) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($manualDisabledClasses->isNotEmpty())
            <div>
                <h3 class="font-bold text-15px text-primary mb-3">وصول معطّل ({{ $manualDisabledClasses->count() }})</h3>
                <div class="overflow-x-auto border border-d9 rounded-12px">
                    <table class="table w-full text-13px">
                        <thead>
                            <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                                <th class="px-3 py-3 text-start">الدورة</th>
                                <th class="px-3 py-3 text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($manualDisabledClasses as $sale)
                                <tr class="border-b border-d9 last:border-0">
                                    <td class="px-3 py-3 font-semibold text-primary">{{ $sale->webinar->title ?? '—' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <form method="POST" action="{{ route('panel.v1.admin.system.users.enable-course', ['id' => $editUser->id, 'saleId' => $sale->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="font-semibold text-13px text-[#059669] hover:underline">إعادة التفعيل</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(() => {
    const toggle = document.querySelector('[data-ban-toggle]');
    const dates = document.querySelector('[data-ban-dates]');
    if (!toggle || !dates) return;
    const sync = () => dates.classList.toggle('hidden', !toggle.checked);
    toggle.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
