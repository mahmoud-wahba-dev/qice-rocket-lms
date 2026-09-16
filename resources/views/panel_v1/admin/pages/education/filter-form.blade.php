@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($filter) && $filter; @endphp
<div class="space-y-6 pb-8 max-w-4xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل فلتر: '.$filter->title : 'فلتر جديد','subtitle'=>'منطق Admin\\FilterController حرفياً — تصنيف + خيارات فرعية'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction ?? route('panel.v1.admin.education.filters.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px"><option value="ar" @selected(old('locale','ar')=='ar')>العربية</option><option value="en" @selected(old('locale')=='en')>English</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">اسم الفلتر *</label><input type="text" name="title" value="{{ old('title', $filter->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: مستوى المهارة">@error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">التصنيف *</label><select name="category_id" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px" required><option value="">اختر التصنيف</option>@foreach($categories ?? [] as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $filter->category_id ?? '')==$cat->id)>{{ $cat->title }}</option>@endforeach</select>@error('category_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
            </div>

            <div class="border-t border-d9 pt-5">
                <div class="flex justify-between items-center mb-3"><label class="font-bold text-14px text-primary">الخيارات الفرعية (Filter Options)</label><button type="button" id="addOpt" class="btn btn-sm rounded-10px border-d9 bg-white">+ إضافة خيار</button></div>
                <div id="filterOpts" class="space-y-3">
                    @php $opts = old('sub_filters', isset($filterOptions) && $filterOptions->count() ? $filterOptions->map(fn($o)=>['id'=>$o->id,'title'=>$o->title])->all() : []); @endphp
                    @foreach($opts as $key => $opt)
                    <div class="opt-item border border-d9 rounded-12px p-4 bg-[#FAFAF4] flex gap-3 items-start"><button type="button" onclick="this.closest('.opt-item').remove()" class="text-red-500 text-14px mt-2">✕</button><div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3"><div class="sm:col-span-2"><label class="text-12px font-medium">اسم الخيار *</label><input type="text" name="sub_filters[{{ is_numeric($key)?$key:'new_'.$key }}][title]" value="{{ is_array($opt)?($opt['title']??''):$opt }}" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px" required></div></div></div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الفلتر' }}</button>
                <a href="{{ route('panel.v1.admin.education.filters.list') }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
<script>
let optIdx=Date.now();
document.getElementById('addOpt')?.addEventListener('click', ()=>{
    const box=document.getElementById('filterOpts');
    box.insertAdjacentHTML('beforeend', `<div class="opt-item border border-d9 rounded-12px p-4 bg-[#FAFAF4] flex gap-3 items-start"><button type="button" onclick="this.closest('.opt-item').remove()" class="text-red-500 text-14px mt-2">✕</button><div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3"><div class="sm:col-span-2"><label class="text-12px font-medium">اسم الخيار *</label><input type="text" name="sub_filters[new_${optIdx++}][title]" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px" required></div></div></div>`);
});
</script>
@endsection
