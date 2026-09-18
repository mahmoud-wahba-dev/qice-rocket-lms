@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? $pageTitle ?? 'المبيعات',
        'subtitle' => $pageSubtitle ?? '',
    ])

    @include('panel_v1.admin.components.filter-bar')

    {{-- Budget stats (الميزانية) --}}
    @if (!empty($budget))
        @include('panel_v1.admin.components.stats-cards', ['stats' => [
            ['label' => 'إجمالي المبيعات', 'value' => $budget['total'] ?? '0', 'icon' => 'icon-[tabler--receipt]'],
            ['label' => 'عدد العمليات', 'value' => (string)($budget['count'] ?? 0), 'icon' => 'icon-[tabler--chart-bar]'],
        ]])
    @endif

    {{-- Payouts جدول طلبات السحب --}}
    @if (!empty($payouts))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">طلبات السحب</h2>
                <span class="font-medium text-13px text-gray">{{ is_array($payouts) ? count($payouts) : $payouts->count() }} طلب</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">رقم</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">المبلغ</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($payouts as $p)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $p['id'] ?? $p->id ?? '#' }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $p['user'] ?? $p->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ $p['amount'] ?? handlePrice($p->amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'inline-flex rounded-full px-3 py-1 font-semibold text-11px',
                                    'bg-[#D1FAE5] text-[#059669]' => in_array($p['status'] ?? $p->status ?? '', ['done','approved','paid']),
                                    'bg-[#FEF3C7] text-[#D97706]' => in_array($p['status'] ?? $p->status ?? '', ['pending','waiting']),
                                    'bg-[#FEE2E2] text-[#DC2626]' => in_array($p['status'] ?? $p->status ?? '', ['rejected','canceled']),
                                    'bg-[#EFF6FF] text-[#2563EB]' => !in_array($p['status'] ?? $p->status ?? '', ['done','approved','paid','pending','waiting','rejected','canceled']),
                                ])>{{ $p['status'] ?? $p->status ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $p['date'] ?? date('Y/m/d',(int)($p->created_at ?? time())) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <form method="POST" action="{{ route('panel.v1.admin.sales.payouts.approve',['id'=> trim($p['id']??'',' #')]) }}" class="inline">@csrf<button type="submit" class="size-7 rounded-8px bg-[#D1FAE5] center" title="موافقة"><span class="icon-[tabler--check] size-3 text-[#059669]"></span></button></form>
                                    <form method="POST" action="{{ route('panel.v1.admin.sales.payouts.reject',['id'=> trim($p['id']??'',' #')]) }}" class="inline">@csrf<button type="submit" class="size-7 rounded-8px bg-[#FEE2E2] center" title="رفض"><span class="icon-[tabler--x] size-3 text-[#DC2626]"></span></button></form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($offlinePayments) && $offlinePayments->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">المدفوعات دون اتصال</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">المبلغ</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach ($offlinePayments as $op)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $op->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $op->user->full_name ?? $op->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($op->amount ?? $op->total_amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#EFF6FF] text-[#2563EB] px-3 py-1 font-semibold text-11px">{{ $op->status ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)($op->created_at ?? time())) }}</td>
                            <td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><form method="POST" action="{{ route('panel.v1.admin.sales.offline.approve',['id'=>$op->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#D1FAE5] center hover:opacity-90" title="تأكيد"><span class="icon-[tabler--check] size-4 text-[#059669]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.sales.offline.reject',['id'=>$op->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#FEE2E2] center hover:opacity-90" title="رفض"><span class="icon-[tabler--x] size-4 text-[#DC2626]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.sales.offline.delete',['id'=>$op->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($subscriptions) && $subscriptions->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">خطط الاشتراك</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الخطة</th><th class="px-4 py-3 text-center">السعر</th><th class="px-4 py-3 text-center">المدة</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($subscriptions as $s)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $s->title ?? 'اشتراك #'.$s->id }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($s->price ?? 0) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $s->days ?? '—' }} يوم</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $s->status ?? 'نشط' }}</span></td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.sales.subscriptions.delete',['id'=>$s->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($installments) && $installments->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">طلبات التقسيط</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">المبلغ</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($installments as $ins)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $ins->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $ins->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($ins->total_amount ?? $ins->amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#FEF3C7] text-[#D97706] px-3 py-1 font-semibold text-11px">{{ $ins->status ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)($ins->created_at ?? time())) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($packages) && $packages->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">باقات الخدمات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الباقة</th><th class="px-4 py-3 text-center">السعر</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($packages as $pkg)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $pkg->title ?? 'باقة #'.$pkg->id }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($pkg->price ?? 0) }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $pkg->status ?? 'نشط' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($meetings))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">باقات الاجتماعات</h2>
                <a href="{{ route('panel.v1.admin.sales.meeting-packages.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ باقة جديدة</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">السعر</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @forelse ($meetings as $m)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $m->id }}</td>
                            <td class="px-4 py-3 font-bold text-primary">{{ $m->title ?? $m->name ?? 'اجتماع #'.$m->id }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($m->price ?? $m->amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ ($m->enable ?? false) ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ ($m->enable ?? false) ? 'مفعّل' : 'معطل' }}</span></td>
                            <td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><a href="{{ route('panel.v1.admin.sales.meeting-packages.edit',['id'=>$m->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تعديل"><span class="icon-[tabler--edit] size-4 text-primary"></span></a><form method="POST" action="{{ route('panel.v1.admin.sales.meeting-packages.delete',['id'=>$m->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد باقات اجتماعات — أنشئ أول باقة</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($documents) && $documents->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">المستندات والأرصدة</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">المبلغ</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($documents as $doc)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $doc->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $doc->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($doc->amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $doc->type ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$doc->created_at) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($paymentChannels) && $paymentChannels->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">قنوات الدفع — {{ $paymentChannels->total() }}</h2>
                <span class="font-medium text-12px text-gray">منطق Admin\PaymentChannelController</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($paymentChannels as $ch)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $ch->title }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ ($ch->status ?? '')=='active' ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $ch->status ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><a href="{{ route('panel.v1.admin.sales.payment-channels.edit',['id'=>$ch->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تعديل"><span class="icon-[tabler--edit] size-4 text-primary"></span></a><form method="POST" action="{{ route('panel.v1.admin.sales.payment-channels.toggle',['id'=>$ch->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تفعيل/تعطيل"><span class="icon-[tabler--repeat] size-4 text-primary"></span></button></form></div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($soldStats))
        @include('panel_v1.admin.components.stats-cards', ['stats' => $soldStats])
    @endif

    @if (!empty($meetingPackagesSold) && $meetingPackagesSold->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">باقات الاجتماعات المباعة — {{ $meetingPackagesSold->total() }}</h2>
                <div class="flex flex-wrap items-center gap-2"><form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">@foreach(request()->except(['status','page']) as $k=>$v) @if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach<select name="status" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px bg-white"><option value="">كل الحالات</option><option value="open" @selected(request('status')=='open')>مفتوحة</option><option value="finished" @selected(request('status')=='finished')>منتهية</option></select></form><span class="font-medium text-12px text-gray">منطق Admin\MeetingPackagesSoldController</span></div>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الطالب</th><th class="px-4 py-3 text-start">المدرب</th><th class="px-4 py-3 text-center">الباقة</th><th class="px-4 py-3 text-center">المدفوع</th><th class="px-4 py-3 text-center">الجلسات (كلي/منتهي/مجدول)</th><th class="px-4 py-3 text-center">الشراء</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($meetingPackagesSold as $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-medium text-primary">{{ $row->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $row->meetingPackage->creator->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-primary">{{ $row->meetingPackage->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($row->paid_amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $row->sessions_count ?? $row->sessions->count() }} / {{ $row->ended ?? '—' }} / {{ $row->scheduled ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)($row->paid_at ?? $row->created_at)) }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ ($row->status ?? '')=='finished' ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEF3C7] text-[#D97706]' }}">{{ $row->status ?? '—' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($eventSoldTickets) && $eventSoldTickets->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">تذاكر الفعاليات المباعة — {{ $eventSoldTickets->total() }}</h2>
                <div class="flex flex-wrap items-center gap-2"><form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">@foreach(request()->except(['ticket_id','page']) as $k=>$v) @if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach<select name="ticket_id" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px bg-white min-w-[10rem]"><option value="">كل التذاكر</option>@foreach($allTickets ?? [] as $tk)<option value="{{ $tk->id }}" @selected((string)request('ticket_id')===(string)$tk->id)>{{ $tk->title ?? 'تذكرة #'.$tk->id }}</option>@endforeach</select>@if(request('ticket_id'))<a href="{{ url()->current() }}" class="h-10 px-3 rounded-10px border border-d9 bg-white text-12px font-semibold text-primary center">مسح</a>@endif</form><a href="{{ route('panel.v1.admin.sales.event-tickets.export', request()->query()) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"><span class="icon-[tabler--file-spreadsheet] size-4"></span> تصدير Excel</a></div>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الفعالية</th><th class="px-4 py-3 text-start">المشارك</th><th class="px-4 py-3 text-center">نوع التذكرة</th><th class="px-4 py-3 text-center">المدفوع</th><th class="px-4 py-3 text-center">الكود</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead>
                    <tbody>
                    @foreach ($eventSoldTickets as $t)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $t->eventTicket->event->title ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $t->user->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $t->eventTicket->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($t->paid_amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center font-mono text-12px text-gray">{{ $t->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)($t->paid_at ?? $t->created_at)) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (empty($payouts) && empty($budget) && empty($offlinePayments) && empty($subscriptions) && empty($installments) && empty($packages) && empty($meetings) && empty($documents) && empty($paymentChannels) && empty($meetingPackagesSold) && empty($eventSoldTickets))
        @include('panel_v1.admin.components.empty-stub', ['title' => $stubTitle ?? 'لا توجد بيانات', 'subtitle' => $stubSubtitle ?? 'لم يتم العثور على سجلات لهذا القسم.'])
    @endif

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
