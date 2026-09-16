@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($category) && $category; @endphp
<div class="space-y-6 pb-8 max-w-4xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل قسم: '.$category->title : 'إنشاء قسم جديد','subtitle'=>'منطق Admin\CategoryController حرفياً — ترتيب + أبناء + ترجمة'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction ?? route('panel.v1.admin.education.departments.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">اللغة *</label><select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px"><option value="ar" @selected(old('locale','ar')=='ar')>العربية</option><option value="en" @selected(old('locale')=='en')>English</option></select></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الترتيب *</label><input type="number" name="order" value="{{ old('order', $category->order ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="1"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">اسم القسم *</label><input type="text" name="title" value="{{ old('title', $category->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">@error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الرابط (slug)</label><input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="auto" dir="ltr">@error('slug')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror</div>
                <div class="flex items-center gap-2 pt-6"><label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="enable" value="on" @checked(old('enable', $category->enable ?? true)) class="checkbox checkbox-sm"> مفعّل</label></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الأيقونة</label><input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="/assets/..."></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">أيقونة 2</label><input type="text" name="icon2" value="{{ old('icon2', $category->icon2 ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">لون صندوق أيقونة 2</label><input type="color" name="icon2_box_color" value="{{ old('icon2_box_color', $category->icon2_box_color ?? '#0F3D36') }}" class="input input-bordered w-full h-12 rounded-12px border-d9"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">صورة الغلاف</label><input type="text" name="cover_image" value="{{ old('cover_image', $category->cover_image ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">صورة التراكب</label><input type="text" name="overlay_image" value="{{ old('overlay_image', $category->overlay_image ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">العنوان السفلي SEO</label><input type="text" name="bottom_seo_title" value="{{ old('bottom_seo_title', $category->bottom_seo_title ?? '') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div class="sm:col-span-2"><label class="font-semibold text-14px text-primary mb-2 block">وصف SEO</label><textarea name="bottom_seo_content" rows="3" class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px">{{ old('bottom_seo_content', $category->bottom_seo_content ?? '') }}</textarea></div>
            </div>

            <div class="border-t border-d9 pt-5">
                <label class="flex items-center gap-2 font-bold text-14px text-primary mb-3"><input type="checkbox" name="has_sub" value="on" id="has_sub" @checked(old('has_sub', isset($subCategories) && $subCategories->count() ? 'on' : '')) class="checkbox checkbox-sm"> يحتوي أقسام فرعية</label>
                <div id="subCategoriesBox" class="space-y-3 @if(empty($subCategories) || !$subCategories->count()) hidden @endif">
                    @if(!empty($subCategories))
                        @foreach($subCategories as $sub)
                            <div class="sub-item border border-d9 rounded-12px p-4 bg-[#FAFAF4] space-y-3">
                                <div class="flex justify-between items-center"><span class="font-semibold text-13px">قسم فرعي #{{ $sub->id }}</span><button type="button" onclick="this.closest('.sub-item').remove()" class="text-red-500 text-12px">حذف</button></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div><label class="text-12px font-medium">العنوان *</label><input type="text" name="sub_categories[{{ $sub->id }}][title]" value="{{ $sub->title }}" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px" required></div>
                                    <div><label class="text-12px font-medium">Slug</label><input type="text" name="sub_categories[{{ $sub->id }}][slug]" value="{{ $sub->slug }}" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px" dir="ltr"></div>
                                    <div><label class="text-12px font-medium">الأيقونة</label><input type="text" name="sub_categories[{{ $sub->id }}][icon]" value="{{ $sub->icon }}" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px"></div>
                                    <div><label class="text-12px font-medium">مفعّل</label><select name="sub_categories[{{ $sub->id }}][enable]" class="select select-bordered w-full h-10 rounded-10px border-d9 text-13px"><option value="on" @selected($sub->enable)>نعم</option><option value="">لا</option></select></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div id="newSubs"></div>
                    <button type="button" id="addSub" class="btn btn-sm rounded-10px border-d9 bg-white">+ إضافة قسم فرعي</button>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء القسم' }}</button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'departments']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('has_sub')?.addEventListener('change', e=>{ document.getElementById('subCategoriesBox').classList.toggle('hidden', !e.target.checked); });
let subIdx=Date.now();
document.getElementById('addSub')?.addEventListener('click', ()=>{
  const box=document.getElementById('newSubs'); const id='new_'+(subIdx++);
  box.insertAdjacentHTML('beforeend', `<div class="sub-item border border-d9 rounded-12px p-4 bg-[#FAFAF4] space-y-3"><div class="flex justify-between"><span class="font-semibold text-13px">جديد</span><button type="button" onclick="this.closest('.sub-item').remove()" class="text-red-500 text-12px">حذف</button></div><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><div><label class="text-12px font-medium">العنوان *</label><input type="text" name="sub_categories[${id}][title]" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px" required></div><div><label class="text-12px font-medium">Slug</label><input type="text" name="sub_categories[${id}][slug]" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px" dir="ltr"></div><div><label class="text-12px font-medium">الأيقونة</label><input type="text" name="sub_categories[${id}][icon]" class="input input-bordered w-full h-10 rounded-10px border-d9 text-13px"></div><div><label class="text-12px font-medium">مفعّل</label><select name="sub_categories[${id}][enable]" class="select select-bordered w-full h-10 rounded-10px border-d9 text-13px"><option value="on" selected>نعم</option><option value="">لا</option></select></div></div></div>`);
});
</script>
@endsection
