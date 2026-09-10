@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $select = 'select select-bordered w-full h-12 sm:h-14 min-h-12 sm:min-h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $textarea = 'textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary min-h-28 resize-y';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
@endphp

{{-- Course type --}}
<section class="{{ $card }}">
    <div class="flex items-center gap-3 mb-5 sm:mb-6">
        <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--folder] size-5 text-primary"></span>
        </span>
        <h2 class="font-bold text-18px sm:text-20px text-primary">نوع الدورة التدريبية</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4" data-course-type-group>
        <input type="hidden" name="course_type" value="recorded" data-course-type-value>
        @foreach ($courseTypes ?? [] as $type)
            <button type="button" data-course-type="{{ $type['key'] }}"
                class="group relative text-start rounded-14px border p-4 sm:p-5 transition
                    {{ ($type['key'] ?? '') === 'recorded'
                        ? 'border-primary bg-[#F7F0E6]'
                        : 'border-d9 bg-white hover:border-primary/40' }}">
                <span data-type-check
                    class="absolute top-3 end-3 size-6 rounded-full bg-primary text-white center {{ ($type['key'] ?? '') === 'recorded' ? '' : 'hidden' }}">
                    <span class="icon-[tabler--check] size-3.5"></span>
                </span>
                <span class="size-11 rounded-12px bg-primary/10 center mb-3">
                    @if (($type['key'] ?? '') === 'live')
                        <span class="icon-[tabler--video] size-5 text-primary"></span>
                    @elseif (($type['key'] ?? '') === 'text')
                        <span class="icon-[tabler--file-text] size-5 text-primary"></span>
                    @else
                        <span class="icon-[tabler--player-play] size-5 text-primary"></span>
                    @endif
                </span>
                <p class="font-bold text-16px sm:text-17px text-primary mb-1">{{ $type['label'] }}</p>
                <p class="font-medium text-13px sm:text-14px text-gray leading-relaxed">{{ $type['hint'] }}</p>
            </button>
        @endforeach
    </div>
</section>

{{-- Basic info --}}
<section class="{{ $card }}">
    <div class="flex items-center gap-3 mb-5 sm:mb-6">
        <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--list-details] size-5 text-primary"></span>
        </span>
        <h2 class="font-bold text-18px sm:text-20px text-primary">المعلومات الأساسية</h2>
    </div>
    <div class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <div>
                <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">لغة الدورة</label>
                <select name="locale" class="{{ $select }}">
                    @foreach ($languages ?? [['key' => 'ar', 'label' => 'العربية']] as $lang)
                        <option value="{{ $lang['key'] }}" {{ old('locale', $draft['locale'] ?? 'ar') === $lang['key'] ? 'selected' : '' }}>{{ $lang['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">التصنيف الرئيسي</label>
                <select name="category_id" class="{{ $select }}">
                    <option value="">اختر التصنيف</option>
                    @foreach ($categories ?? [] as $cat)
                        <option value="{{ $cat['id'] }}" {{ (string) old('category_id', $draft['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' }}>{{ $cat['title'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">عنوان الدورة <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $draft['title'] ?? '') }}" class="{{ $input }}" placeholder="أدخل عنوان الدورة">
        </div>
        <div data-tag-input>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">الوسوم</label>
            <div class="flex flex-wrap items-center gap-2 min-h-12 sm:min-h-14 rounded-10px border border-d9 bg-white px-3 py-2">
                <div class="flex flex-wrap gap-2" data-tag-list>
                    @foreach ($tags ?? [] as $tag)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 font-medium text-13px text-primary" data-tag>
                            {{ $tag }}
                            <button type="button" class="hover:opacity-70" data-tag-remove aria-label="حذف وسم">
                                <span class="icon-[tabler--x] size-3.5"></span>
                            </button>
                        </span>
                    @endforeach
                </div>
                <input type="text" data-tag-field
                    class="flex-1 min-w-32 border-0 bg-transparent font-medium text-15px text-black focus:outline-none py-1"
                    placeholder="أضف وسمًا ثم اضغط Enter">
                <input type="hidden" name="tags" value="{{ old('tags', $draft['tags'] ?? '') }}" data-tags-value>
            </div>
        </div>
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">الوصف المختصر / Meta Description <span class="text-red-500">*</span></label>
            <textarea rows="3" name="seo_description" class="{{ $textarea }}" data-meta-desc maxlength="160"
                placeholder="وصف قصير يظهر في نتائج البحث...">{{ old('seo_description', $draft['seo_description'] ?? '') }}</textarea>
            <p class="mt-2 text-end font-medium text-13px text-gray"><span data-meta-count>0</span>/160</p>
        </div>
    </div>
</section>

{{-- Media --}}
<section class="{{ $card }}">
    <div class="flex items-center gap-3 mb-5 sm:mb-6">
        <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--photo] size-5 text-primary"></span>
        </span>
        <h2 class="font-bold text-18px sm:text-20px text-primary">الوسائط والصور</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-6">
        @foreach ([['الصورة المصغرة', 'thumbnail'], ['غلاف الدورة', 'cover']] as [$label, $key])
            <label class="flex flex-col items-center justify-center gap-3 min-h-44 rounded-14px border border-dashed border-d9 bg-[#F7F0E6]/40 px-4 py-8 cursor-pointer hover:border-primary/40 transition">
                <span class="icon-[tabler--cloud-upload] size-8 text-primary"></span>
                <span class="font-semibold text-15px text-primary">{{ $label }}</span>
                <span class="inline-flex items-center h-10 px-4 rounded-10px bg-primary text-white font-semibold text-14px">اختر ملفًا</span>
                <input type="file" name="image_{{ $key }}" class="hidden" accept="image/*" data-upload="{{ $key }}">
            </label>
        @endforeach
    </div>
    <div>
        <p class="font-semibold text-15px sm:text-16px text-primary mb-3">الفيديو الترويجي</p>
        <div class="inline-flex rounded-10px border border-d9 overflow-hidden mb-4" data-promo-tabs>
            <button type="button" data-promo-tab="link"
                class="px-4 py-2.5 font-semibold text-14px bg-primary text-white">رابط خارجي (YouTube/Vimeo)</button>
            <button type="button" data-promo-tab="file"
                class="px-4 py-2.5 font-semibold text-14px text-gray bg-white hover:bg-[#FAFAF4]">رفع ملف فيديو</button>
        </div>
        <div data-promo-panel="link">
            <input type="url" name="video_demo_link" value="{{ old('video_demo_link', $draft['video_demo_link'] ?? '') }}" class="{{ $input }}" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div data-promo-panel="file" class="hidden">
            <label class="flex items-center justify-center gap-2 h-14 rounded-10px border border-dashed border-d9 cursor-pointer hover:bg-primary/5 transition font-semibold text-15px text-primary">
                <span class="icon-[tabler--upload] size-5"></span>
                اختر ملف فيديو
                <input type="file" class="hidden" accept="video/*">
            </label>
        </div>
    </div>
</section>

{{-- Description --}}
<section class="{{ $card }}">
    <div class="flex items-center gap-3 mb-5 sm:mb-6">
        <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--file-description] size-5 text-primary"></span>
        </span>
        <h2 class="font-bold text-18px sm:text-20px text-primary">الوصف التفصيلي للدورة</h2>
    </div>
    <div class="rounded-12px border border-d9 overflow-hidden">
        <div class="flex flex-wrap gap-1 border-b border-d9 bg-[#FAFAF4] px-3 py-2">
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="عريض">
                <span class="icon-[tabler--bold] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="مائل">
                <span class="icon-[tabler--italic] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="تسطير">
                <span class="icon-[tabler--underline] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="رابط">
                <span class="icon-[tabler--link] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="قائمة">
                <span class="icon-[tabler--list] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="قائمة مرقمة">
                <span class="icon-[tabler--list-numbers] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="محاذاة يمين">
                <span class="icon-[tabler--align-right] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="توسيط">
                <span class="icon-[tabler--align-center] size-4"></span>
            </button>
            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-white transition" tabindex="-1" aria-label="محاذاة يسار">
                <span class="icon-[tabler--align-left] size-4"></span>
            </button>
        </div>
        <textarea rows="10" name="description" class="w-full border-0 focus:outline-none px-4 py-4 font-medium text-15px sm:text-16px text-black min-h-48 resize-y"
            placeholder="اكتب وصف الدورة التفصيلي هنا...">{{ old('description', $draft['description'] ?? '') }}</textarea>
    </div>
</section>

{{-- Extra settings --}}
<section class="{{ $card }}">
    <div class="flex items-center gap-3 mb-5 sm:mb-6">
        <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--adjustments-horizontal] size-5 text-primary"></span>
        </span>
        <h2 class="font-bold text-18px sm:text-20px text-primary">إعدادات إضافية</h2>
    </div>
    <div class="divide-y divide-d9">
        <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
            <div class="min-w-0 text-start">
                <p class="font-semibold text-15px sm:text-16px text-primary mb-1">السماح للطلاب بتحميل الملفات</p>
                <p class="font-medium text-13px sm:text-14px text-gray">تمكين تنزيل المرفقات والمواد المرتبطة بالدورة</p>
            </div>
            <input type="checkbox" class="switch switch-primary shrink-0" checked aria-label="السماح بتحميل الملفات">
        </div>
        <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
            <div class="min-w-0 text-start">
                <p class="font-semibold text-15px sm:text-16px text-primary mb-1">إضافة مدرب مشارك</p>
                <p class="font-medium text-13px sm:text-14px text-gray">دعوة مدرب آخر للمساعدة في إدارة الدورة</p>
            </div>
            <input type="checkbox" class="switch switch-primary shrink-0" aria-label="إضافة مدرب مشارك">
        </div>
    </div>
</section>
