@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', ['title'=>'خطط التقسيط','subtitle'=> ($paginator->total() ?? 0).' خطة'])
    <div class="border border-d9 rounded-14px bg-white p-4 sm:p-6">
        <div class="flex justify-end mb-4"><a href="{{ route('panel.v1.admin.sales.installments.create') }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-12px bg-primary text-white font-bold text-13px">+ إنشاء خطة</a></div>
        <div class="overflow-x-auto">
            <table class="table w-full text-13px">
                <thead><tr class="border-b border-d9 bg-f9 text-gray"><th class="px-3 py-3 text-start">#</th><th class="px-3 py-3 text-start">العنوان</th><th class="px-3 py-3 text-start">الهدف</th><th class="px-3 py-3 text-start">المبيعات</th><th class="px-3 py-3 text-start">الإجراءات</th></tr></thead>
                <tbody>
                    @foreach(($installments ?? $paginator ?? []) as $row)
                    <tr class="border-b border-d9"><td class="px-3 py-3">#{{ $row->id }}</td><td class="px-3 py-3 font-semibold text-primary">{{ $row->title ?? '—' }}</td><td class="px-3 py-3">{{ $row->target_type }}</td><td class="px-3 py-3">{{ $row->sales_count ?? 0 }}</td><td class="px-3 py-3 flex gap-2"><a href="{{ route('panel.v1.admin.sales.installments.edit',['id'=>$row->id]) }}" class="btn btn-sm rounded-10px border border-d9 bg-white text-12px">تعديل</a><form method="POST" action="{{ route('panel.v1.admin.sales.installments.plans.delete',['id'=>$row->id]) }}">@csrf<button class="btn btn-sm rounded-10px bg-red-500 text-white text-12px" onclick="return confirm('حذف؟')">حذف</button></form></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('panel_v1.admin.components.pagination', ['paginator'=>$paginator ?? null])
    </div>
</div>
@endsection
