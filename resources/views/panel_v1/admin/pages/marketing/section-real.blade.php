@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? $pageTitle ?? 'التسويق',
        'subtitle' => $pageSubtitle ?? '',
    ])

    @include('panel_v1.admin.components.filter-bar')

    @if (!empty($stats))
        @include('panel_v1.admin.components.stats-cards', ['stats' => $stats])
    @endif

    @if (!empty($discounts) && (is_array($discounts) ? count($discounts) : $discounts->count()))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">القسائم</h2>
                <a href="{{ route('panel.v1.admin.marketing.discounts.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة قسيمة</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الكود</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">النسبة</th><th class="px-4 py-3 text-center">العدد المتبقي</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($discounts as $d)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $d->code ?? $d['code'] ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $d->title ?? $d['title'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ $d->percent ?? $d['percent'] ?? '—' }}%</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $d->count ?? $d['count'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $d->status ?? 'نشط' }}</span></td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.marketing.discounts.delete',['id'=>$d->id ?? $d['id'] ?? 0]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($affiliates) && $affiliates->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">التسويق بالعمولة</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($affiliates as $a)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $a->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $a->title ?? 'عمولة #'.$a->id }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#EFF6FF] text-[#2563EB] px-3 py-1 font-semibold text-11px">{{ $a->status ?? '—' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($cashbacks) && $cashbacks->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">الاسترداد النقدي</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">القاعدة</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($cashbacks as $c)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $c->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $c->title ?? 'قاعدة #'.$c->id }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $c->status ?? 'نشط' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($points) && $points->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">نقاط المكافآت</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">النقاط</th><th class="px-4 py-3 text-center">النوع</th></tr></thead>
                    <tbody>
                    @foreach ($points as $p)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $p->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $p->user->full_name ?? $p->user_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ $p->score ?? $p->amount ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $p->type ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($gifts) && $gifts->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">الهدايا</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">الهدية</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($gifts as $g)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $g->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $g->title ?? 'هدية #'.$g->id }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $g->status ?? 'نشط' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($bonuses) && $bonuses->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">مكافآت التسجيل</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المكافأة</th><th class="px-4 py-3 text-center">النقاط</th></tr></thead>
                    <tbody>
                    @foreach ($bonuses as $b)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $b->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $b->title ?? 'مكافأة #'.$b->id }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ $b->score ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($products) && $products->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">منتجات المتجر</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المنتج</th><th class="px-4 py-3 text-center">السعر</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($products as $pd)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $pd->title ?? 'منتج #'.$pd->id }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ $pd->price ? handlePrice($pd->price) : '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $pd->status ?? 'نشط' }}</span></td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.marketing.products.delete',['id'=>$pd->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($blogs) && $blogs->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">المدونة</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المقال</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($blogs as $bg)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $bg->title ?? 'مقال #'.$bg->id }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $bg->status ?? 'نشط' }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$bg->created_at) }}</td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.marketing.blog.delete',['id'=>$bg->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($pages) && $pages->count())
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">الصفحات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الصفحة</th><th class="px-4 py-3 text-center">الرابط</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach ($pages as $pg)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $pg->title ?? 'صفحة #'.$pg->id }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray" dir="ltr">{{ $pg->link ?? $pg->slug ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.marketing.pages.delete',['id'=>$pg->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ((empty($discounts) || (is_array($discounts) ? count($discounts)===0 : $discounts->count()===0))
        && (empty($affiliates) || $affiliates->count()===0)
        && (empty($cashbacks) || $cashbacks->count()===0)
        && (empty($points) || $points->count()===0)
        && (empty($gifts) || $gifts->count()===0)
        && (empty($bonuses) || $bonuses->count()===0)
        && (empty($products) || $products->count()===0)
        && (empty($blogs) || $blogs->count()===0)
        && (empty($pages) || $pages->count()===0)
        && empty($stats))
        @include('panel_v1.admin.components.empty-stub', ['title' => $stubTitle ?? 'لا توجد بيانات', 'subtitle' => $stubSubtitle ?? 'لم يتم العثور على سجلات لهذا القسم.'])
    @endif

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
