@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? $pageTitle ?? 'النظام',
        'subtitle' => $pageSubtitle ?? '',
    ])

    @include('panel_v1.admin.components.filter-bar')

    @if (!empty($settings) && $settings->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">إعدادات النظام</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المفتاح</th><th class="px-4 py-3 text-start">القيمة</th><th class="px-4 py-3 text-center">حفظ</th></tr></thead>
                    <tbody>
                    @foreach ($settings as $s)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $s->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $s->name ?? $s->key ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('panel.v1.admin.system.settings.save',['id'=>$s->id]) }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="value" value="{{ $s->value }}" class="input input-bordered flex-1 h-10 rounded-10px border-d9 text-13px min-w-0" dir="ltr">
                                    <button type="submit" class="h-10 px-4 rounded-10px bg-primary text-white font-bold text-12px shrink-0 hover:opacity-95 transition">حفظ</button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-gray text-12px">يُحفظ من حقل القيمة</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($roles) && $roles->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">الأدوار</h2>
                <a href="{{ route('panel.v1.admin.system.roles.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة دور</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">الاسم</th><th class="px-4 py-3 text-center">العدد</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($roles as $r)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $r->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $r->name ?? $r->caption ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $r->users_count ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.system.roles.delete',['id'=>$r->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($access) && $access->count() && empty($roles))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">إدارة الوصول</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدور</th><th class="px-4 py-3 text-center">الصلاحيات</th></tr></thead>
                    <tbody>
                    @foreach ($access as $a)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $a->name ?? $a->caption ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $a->caption ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($groups) && $groups->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">المجموعات</h2>
                <a href="{{ route('panel.v1.admin.system.groups.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة مجموعة</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المجموعة</th><th class="px-4 py-3 text-center">الخصم</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($groups as $g)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $g->name ?? 'مجموعة #'.$g->id }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $g->discount ?? 0 }}%</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $g->status ?? 'نشط' }}</span></td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.system.groups.delete',['id'=>$g->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($badges) && $badges->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">الشارات</h2>
                <a href="{{ route('panel.v1.admin.system.badges.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة شارة</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الشارة</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($badges as $b)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $b->title ?? $b->name ?? 'شارة #'.$b->id }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $b->type ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#EFF6FF] text-[#2563EB] px-3 py-1 font-semibold text-11px">{{ $b->status ?? 'نشط' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($requests) && $requests->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">طلبات انضمام المدربين</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المدرب</th><th class="px-4 py-3 text-start">البريد</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($requests as $r)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $r->full_name }}</td>
                            <td class="px-4 py-3 font-medium text-gray">{{ $r->email }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#FEF3C7] text-[#D97706] px-3 py-1 font-semibold text-11px">{{ $r->status }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$r->created_at) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($deletes) && $deletes->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">طلبات حذف الحساب</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">السبب</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach ($deletes as $d)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $d->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $d->user->full_name ?? $d->user_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ \Illuminate\Support\Str::limit($d->reason ?? '',40) }}</td>
                            <td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><form method="POST" action="{{ route('panel.v1.admin.system.delete-requests.confirm',['id'=>$d->id]) }}" onsubmit="return confirm('تنفيذ حذف الحساب؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#D1FAE5] center hover:opacity-90" title="تنفيذ"><span class="icon-[tabler--check] size-4 text-[#059669]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.system.delete-requests.reject',['id'=>$d->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#FEE2E2] center hover:opacity-90" title="رفض"><span class="icon-[tabler--x] size-4 text-[#DC2626]"></span></button></form></div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($ips) && $ips->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">عناوين IP</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($ips as $ip)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $ip->ip_address ?? $ip->ip ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $ip->type ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#FEE2E2] text-[#DC2626] px-3 py-1 font-semibold text-11px">{{ $ip->status ?? 'محظور' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($tickets) && $tickets->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">نظام التذاكر</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">القسم</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($tickets as $t)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $t->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ \Illuminate\Support\Str::limit($t->title ?? '',40) }}</td>
                            <td class="px-4 py-3 font-medium text-gray">{{ $t->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $t->department->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#FEF3C7] text-[#D97706] px-3 py-1 font-semibold text-11px">{{ $t->status ?? 'open' }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$t->created_at) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($reports) && $reports->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">البلاغات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المُبلّغ</th><th class="px-4 py-3 text-start">السبب</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($reports as $rep)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $rep->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $rep->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray">{{ \Illuminate\Support\Str::limit($rep->reason ?? $rep->message ?? '',50) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$rep->created_at) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($contacts) && $contacts->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">رسائل التواصل</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">الاسم</th><th class="px-4 py-3 text-start">البريد</th><th class="px-4 py-3 text-start">الموضوع</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($contacts as $co)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $co->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $co->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray">{{ $co->email ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-gray">{{ \Illuminate\Support\Str::limit($co->subject ?? $co->title ?? '',30) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$co->created_at) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($consultations) && $consultations->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">الاستشارات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach ($consultations as $con)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $con->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $con->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#EFF6FF] text-[#2563EB] px-3 py-1 font-semibold text-11px">{{ $con->status ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$con->created_at) }}</td>
                            <td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><form method="POST" action="{{ route('panel.v1.admin.system.consultations.finish',['id'=>$con->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#D1FAE5] center hover:opacity-90" title="إنهاء"><span class="icon-[tabler--check] size-4 text-[#059669]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.system.consultations.cancel',['id'=>$con->id]) }}" onsubmit="return confirm('إلغاء الموعد مع الاسترداد؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#FEE2E2] center hover:opacity-90" title="إلغاء"><span class="icon-[tabler--x] size-4 text-[#DC2626]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.system.consultations.delete',['id'=>$con->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($forums) && $forums->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">المنتديات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($forums as $f)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $f->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $f->title ?? $f->slug ?? 'منتدى #'.$f->id }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $f->status ?? 'نشط' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($notifications) && $notifications->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">مركز الإشعارات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($notifications as $n)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $n->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ \Illuminate\Support\Str::limit($n->title ?? '',40) }}</td>
                            <td class="px-4 py-3 font-medium text-gray">{{ $n->user->full_name ?? $n->user_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$n->created_at) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($imports))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">الاستيراد الجماعي (CSV)</h2>
            </div>
            <div class="p-4 sm:p-6 space-y-5">
                <form method="POST" action="{{ route('panel.v1.admin.system.import.validate') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    @csrf
                    <select name="type" required class="select select-bordered h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="courses">دورات</option>
                        <option value="categories">تصنيفات</option>
                        <option value="users">مستخدمون</option>
                        <option value="products">منتجات</option>
                    </select>
                    <input type="file" name="csv_file" accept=".csv" required class="input input-bordered h-12 rounded-12px border-d9 text-13px sm:col-span-2">
                    <button type="submit" class="h-12 px-5 rounded-12px bg-primary text-white font-bold text-13px hover:opacity-95 transition">فحص الملف</button>
                </form>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-13px text-gray">ملفات مثال:</span>
                    @foreach (['courses','categories','users','products'] as $t)
                        <a href="{{ route('panel.v1.admin.system.import.sample',['type'=>$t]) }}" class="h-9 px-3 rounded-10px border border-d9 bg-white font-semibold text-12px text-primary center hover:bg-[#FAFAF4]">{{ $t }}.csv</a>
                    @endforeach
                </div>
                @if(!empty($validItems) && count($validItems))
                    <div class="rounded-12px border border-d9 bg-[#FAFAF4] p-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="font-semibold text-14px text-primary">سجلات صالحة: {{ count($validItems) }} — غير صالحة: {{ $invalidCount ?? 0 }}</p>
                        <form method="POST" action="{{ route('panel.v1.admin.system.import.confirm') }}">@csrf<button type="submit" class="h-11 px-6 rounded-12px bg-[#0FC787] text-white font-bold text-13px hover:opacity-95 transition">تأكيد الاستيراد</button></form>
                    </div>
                @endif
                @if($imports->count())
                <div class="overflow-x-auto">
                    <table class="table w-full text-14px">
                        <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">صالحة</th><th class="px-4 py-3 text-center">غير صالحة</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                        <tbody>
                        @foreach ($imports as $im)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-4 py-3 font-bold text-primary">#{{ $im->id }}</td>
                                <td class="px-4 py-3 text-center font-medium text-primary">{{ $im->data_type }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-[#059669]">{{ $im->valid_items }}</td>
                                <td class="px-4 py-3 text-center font-medium text-gray">{{ $im->invalid_items }}</td>
                                <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$im->created_at) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    @endif

    @if (
        (empty($settings) || $settings->count()===0) &&
        (empty($roles) || $roles->count()===0) &&
        (empty($access) || $access->count()===0) &&
        (empty($groups) || $groups->count()===0) &&
        (empty($badges) || $badges->count()===0) &&
        (empty($requests) || $requests->count()===0) &&
        (empty($deletes) || $deletes->count()===0) &&
        (empty($ips) || $ips->count()===0) &&
        (empty($tickets) || $tickets->count()===0) &&
        (empty($reports) || $reports->count()===0) &&
        (empty($contacts) || $contacts->count()===0) &&
        (empty($consultations) || $consultations->count()===0) &&
        (empty($forums) || $forums->count()===0) &&
        (empty($notifications) || $notifications->count()===0) &&
        (empty($imports) || $imports->count()===0)
    )
        @include('panel_v1.admin.components.empty-stub', ['title' => $stubTitle ?? 'لا توجد بيانات', 'subtitle' => $stubSubtitle ?? 'لم يتم العثور على سجلات لهذا القسم.'])
    @endif

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
