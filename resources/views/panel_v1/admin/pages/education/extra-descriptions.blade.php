@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-4xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'الأوصاف الإضافية','subtitle'=>'مواد التعلم + المتطلبات + شعارات الشركات'])

    <div class="border border-d9 rounded-14px bg-white p-6">
        <h3 class="font-bold text-14px text-primary mb-4">إضافة وصف جديد</h3>
        <form method="POST" action="{{ $formAction }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @csrf
            <select name="type" required class="select select-bordered h-12 rounded-12px border-d9 text-14px bg-white">
                <option value="">اختر النوع</option>
                @foreach($extraTypes ?? [] as $t)
                    <option value="{{ $t }}">{{ match($t) {'learning_materials'=>'مواد التعلم','requirements'=>'المتطلبات','company_logos'=>'شعارات الشركات','default'=>Str::title($t)} }}</option>
                @endforeach
            </select>
            <select name="locale" required class="select select-bordered h-12 rounded-12px border-d9 text-14px bg-white">
                <option value="ar">العربية</option>
                <option value="en">English</option>
            </select>
            <input type="text" name="value" required class="input input-bordered h-12 rounded-12px border-d9 text-14px sm:col-span-2" placeholder="المحتوى">
            <button type="submit" class="h-12 px-6 rounded-12px bg-primary text-white font-bold text-13px hover:opacity-95 transition sm:col-span-4">إضافة</button>
        </form>
    </div>

    @foreach(($extraItems ?? collect()) as $type => $items)
    <div class="border border-d9 rounded-14px bg-white overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
            <h2 class="font-bold text-16px text-primary">{{ match($type) {'learning_materials'=>'مواد التعلم','requirements'=>'المتطلبات','company_logos'=>'شعارات الشركات','default'=>Str::title($type)} }}</h2>
        </div>
        <table class="table w-full text-14px">
            <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المحتوى</th><th class="px-4 py-3">اللغة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                @php $val = $item->translations->where('locale', app()->getLocale())->first()->value ?? $item->translations->first()->value ?? ''; @endphp
                <tr class="border-b border-d9 last:border-0">
                    <td class="px-4 py-3 font-bold text-primary">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium text-primary">{{ \Illuminate\Support\Str::limit($val, 60) }}</td>
                    <td class="px-4 py-3 text-center">
                        @foreach($item->translations as $tr)
                            <span class="inline-flex rounded-full px-2 py-1 font-semibold text-11px bg-[#EFF6FF] text-[#2563EB] mr-1">{{ mb_strtoupper($tr->locale) }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button" onclick="document.getElementById('edit-extra-{{ $item->id }}').classList.toggle('hidden')" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تعديل"><span class="icon-[tabler--edit] size-4 text-primary"></span></button>
                            <form method="POST" action="{{ route('panel.v1.admin.education.extra-descriptions.delete',['id'=>$item->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center hover:bg-red-50" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form>
                        </div>
                    </td>
                </tr>
                <tr id="edit-extra-{{ $item->id }}" class="hidden bg-[#FAFAF4]">
                    <td colspan="4" class="px-4 py-3">
                        @foreach($item->translations as $tr)
                        <form method="POST" action="{{ route('panel.v1.admin.education.extra-descriptions.update',['id'=>$item->id]) }}" class="flex gap-2 mb-2 items-end">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $tr->locale }}">
                            <div class="flex-1">
                                <label class="text-11px font-medium text-gray">{{ mb_strtoupper($tr->locale) }}</label>
                                <input type="text" name="value" value="{{ $tr->value }}" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px">
                            </div>
                            <button type="submit" class="h-10 px-4 rounded-10px bg-primary text-white font-bold text-12px">حفظ</button>
                        </form>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endsection
