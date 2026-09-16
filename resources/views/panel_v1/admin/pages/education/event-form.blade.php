@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => $event ? 'تعديل فعالية' : 'إنشاء فعالية جديدة',
        'subtitle' => $event ? ($event->title ?? '') : 'أدخل بيانات الفعالية — منطق حرفي من Admin\EventsController',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">عنوان الفعالية *</label>
                    <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: ملتقى التعليم 2026">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">العنوان الفرعي *</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $event->subtitle ?? '') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="سطر تعريفي قصير">
                    @error('subtitle')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المنشئ *</label>
                    <select name="creator_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر منشئ</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}" @selected(old('creator_id', $event->creator_id ?? '') == $t->id)>{{ $t->full_name }} (#{{ $t->id }})</option>
                        @endforeach
                    </select>
                    @error('creator_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">التصنيف *</label>
                    <select name="category_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر تصنيف</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat['id'] }}" @selected(old('category_id', $event->category_id ?? '') == $cat['id'])>{{ $cat['title'] }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="online" @selected(old('type', $event->type ?? 'online')=='online')>عن بُعد (online)</option>
                        <option value="in_person" @selected(old('type', $event->type ?? '')=='in_person')>حضوري (in_person)</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة</label>
                    <select name="status" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="draft" @selected(old('status', $event->status ?? 'draft')=='draft')>مسودة</option>
                        <option value="publish" @selected(old('status', $event->status ?? '')=='publish')>منشور</option>
                        <option value="unpublish" @selected(old('status', $event->status ?? '')=='unpublish')>غير منشور</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الصورة المصغرة *</label>
                    <input type="text" name="thumbnail" value="{{ old('thumbnail', $event->thumbnail ?? '/assets/default/img/course_default.jpg') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" dir="ltr">
                    @error('thumbnail')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">صورة الغلاف *</label>
                    <input type="text" name="cover_image" value="{{ old('cover_image', $event->cover_image ?? '/assets/default/img/course_default.jpg') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" dir="ltr">
                    @error('cover_image')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">تاريخ البدء</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', !empty($event->start_date) ? date('Y-m-d\TH:i', (int)$event->start_date) : '') }}"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">تاريخ الانتهاء</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', !empty($event->end_date) ? date('Y-m-d\TH:i', (int)$event->end_date) : '') }}"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">السعة (مقعد)</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity ?? '') }}" min="1"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="فارغ = غير محدود">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المدة (دقيقة)</label>
                    <input type="number" name="duration" value="{{ old('duration', $event->duration ?? '') }}" min="1"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">اللغة</label>
                    <select name="locale" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="ar" @selected(old('locale','ar')=='ar')>العربية</option>
                        <option value="en" @selected(old('locale')=='en')>English</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الوسوم (افصل بفاصلة)</label>
                    <input type="text" name="tags" value="{{ old('tags') }}"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="تعليم, تدريب">
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">ملخص قصير *</label>
                    <textarea name="summary" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('summary', $event->summary ?? '') }}</textarea>
                    @error('summary')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الوصف التفصيلي *</label>
                    <textarea name="description" rows="4" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description', $event->description ?? '') }}</textarea>
                    @error('description')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">وصف SEO *</label>
                    <textarea name="seo_description" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('seo_description', $event->seo_description ?? '') }}</textarea>
                    @error('seo_description')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="support" value="on" @checked(old('support', $event->support ?? false)) class="checkbox checkbox-sm"> دعم</label>
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="certificate" value="on" @checked(old('certificate', $event->certificate ?? false)) class="checkbox checkbox-sm"> شهادة</label>
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="private" value="on" @checked(old('private', $event->private ?? false)) class="checkbox checkbox-sm"> خاص</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                    {{ $event ? 'حفظ التعديلات' : 'إنشاء الفعالية' }}
                </button>
                <a href="{{ route('panel.v1.admin.education.section',['section'=>'events']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>

    @if(!empty($event))
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <h2 class="font-bold text-18px text-primary mb-4">التذاكر</h2>
        @if($event->tickets && $event->tickets->count())
        <div class="overflow-x-auto mb-4">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">التذكرة</th><th class="px-4 py-3 text-center">السعر</th><th class="px-4 py-3 text-center">السعة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                <tbody>
                @foreach($event->tickets as $tk)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $tk->title ?? 'تذكرة #'.$tk->id }}</td><td class="px-4 py-3 text-center">{{ $tk->price ? handlePrice($tk->price) : 'مجانية' }}</td><td class="px-4 py-3 text-center">{{ $tk->capacity ?? '—' }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.events.tickets.delete',['id'=>$tk->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <form method="POST" action="{{ route('panel.v1.admin.education.events.tickets.store',['id'=>$event->id]) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @csrf
            <input type="text" name="title" required placeholder="عنوان التذكرة *" class="input input-bordered h-11 rounded-10px border-d9 text-13px">
            <input type="number" name="price" min="0" placeholder="السعر (0 = مجانية)" class="input input-bordered h-11 rounded-10px border-d9 text-13px">
            <input type="number" name="capacity" min="1" placeholder="السعة" class="input input-bordered h-11 rounded-10px border-d9 text-13px">
            <button type="submit" class="h-11 px-5 rounded-10px bg-primary text-white font-bold text-13px hover:opacity-95 transition">+ إضافة تذكرة</button>
            <input type="hidden" name="description" value="تذكرة فعالية">
        </form>
    </div>

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <h2 class="font-bold text-18px text-primary mb-4">المتحدثون</h2>
        @if($event->speakers && $event->speakers->count())
        <div class="overflow-x-auto mb-4">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الاسم</th><th class="px-4 py-3 text-center">الوظيفة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                <tbody>
                @foreach($event->speakers as $sp)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $sp->name ?? 'متحدث #'.$sp->id }}</td><td class="px-4 py-3 text-center">{{ $sp->job ?? '—' }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.events.speakers.delete',['id'=>$sp->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <form method="POST" action="{{ route('panel.v1.admin.education.events.speakers.store',['id'=>$event->id]) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf
            <input type="text" name="name" required placeholder="اسم المتحدث *" class="input input-bordered h-11 rounded-10px border-d9 text-13px">
            <input type="text" name="job" placeholder="الوظيفة" class="input input-bordered h-11 rounded-10px border-d9 text-13px">
            <button type="submit" class="h-11 px-5 rounded-10px bg-primary text-white font-bold text-13px hover:opacity-95 transition">+ إضافة متحدث</button>
        </form>
    </div>
    @endif
</div>
@endsection
