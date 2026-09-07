@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
@endphp

{{-- Curriculum --}}
<section class="{{ $card }}">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5 sm:mb-6">
        <div class="flex items-center gap-3">
            <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--books] size-5 text-primary"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary">بناء المنهج الدراسي</h2>
        </div>
        <button type="button"
            class="inline-flex items-center gap-2 h-11 px-4 rounded-10px bg-primary text-white font-semibold text-15px hover:opacity-90 transition">
            <span class="icon-[tabler--plus] size-4"></span>
            إضافة وحدة جديدة
        </button>
    </div>

    @foreach ($curriculumUnits ?? [] as $unit)
        <div class="rounded-14px border border-d9 overflow-hidden mb-4 last:mb-0">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-4 bg-[#FAFAF4] border-b border-d9">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="icon-[tabler--grip-vertical] size-5 text-gray shrink-0 cursor-grab"></span>
                    <div class="min-w-0 text-start">
                        <p class="font-bold text-16px sm:text-17px text-primary truncate">{{ $unit['title'] }}</p>
                        <p class="font-medium text-13px text-gray">{{ count($unit['lessons'] ?? []) }} دروس</p>
                    </div>
                </div>
                <button type="button" class="size-9 rounded-8px center text-red-500 hover:bg-red-50 transition" aria-label="حذف الوحدة">
                    <span class="icon-[tabler--trash] size-5"></span>
                </button>
            </div>

            <div class="divide-y divide-d9">
                @foreach ($unit['lessons'] ?? [] as $lesson)
                    <div class="flex flex-wrap items-center gap-3 px-4 sm:px-5 py-3.5">
                        <span class="size-9 rounded-8px bg-primary/10 center shrink-0">
                            @if (($lesson['type'] ?? '') === 'text')
                                <span class="icon-[tabler--file-text] size-4 text-primary"></span>
                            @else
                                <span class="icon-[tabler--player-play] size-4 text-primary"></span>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1 text-start">
                            <p class="font-semibold text-15px sm:text-16px text-primary truncate">{{ $lesson['title'] }}</p>
                            <p class="font-medium text-13px text-gray">{{ $lesson['duration'] }}</p>
                        </div>
                        <label class="inline-flex items-center gap-2 shrink-0">
                            <span class="font-medium text-13px text-gray hidden sm:inline">معاينة مجانية</span>
                            <input type="checkbox" class="switch switch-primary" {{ !empty($lesson['preview']) ? 'checked' : '' }}
                                aria-label="معاينة مجانية">
                        </label>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" class="size-8 rounded-8px center text-primary hover:bg-primary/5" aria-label="تعديل">
                                <span class="icon-[tabler--pencil] size-4"></span>
                            </button>
                            <button type="button" class="size-8 rounded-8px center text-red-500 hover:bg-red-50" aria-label="حذف">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-2 px-4 sm:px-5 py-4 border-t border-d9 bg-white">
                @foreach (['إضافة فيديو', 'إضافة نص', 'إضافة تكليف', 'إضافة مرفق'] as $action)
                    <button type="button"
                        class="inline-flex items-center gap-1.5 h-10 px-3.5 rounded-10px border border-d9 font-semibold text-13px sm:text-14px text-primary hover:bg-[#FAFAF4] transition">
                        <span class="icon-[tabler--plus] size-3.5"></span>
                        {{ $action }}
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach
</section>

{{-- Attachments --}}
<section class="{{ $card }}">
    <div class="flex items-center gap-3 mb-5">
        <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--paperclip] size-5 text-primary"></span>
        </span>
        <div class="text-start">
            <h2 class="font-bold text-18px sm:text-20px text-primary">مرفقات ومصادر الدورة</h2>
            <p class="font-medium text-13px sm:text-14px text-gray mt-1">ملفات مساعدة مثل PDF أو ZIP</p>
        </div>
    </div>
    <label class="flex flex-col items-center justify-center gap-3 min-h-40 rounded-14px border border-dashed border-d9 bg-[#FAFAF4] px-4 py-8 cursor-pointer hover:border-primary/40 transition">
        <span class="icon-[tabler--cloud-upload] size-8 text-primary"></span>
        <span class="font-semibold text-15px text-primary">اسحب الملفات هنا أو اختر ملفات</span>
        <span class="inline-flex items-center h-10 px-4 rounded-10px bg-primary text-white font-semibold text-14px">اختر ملفات</span>
        <input type="file" class="hidden" multiple>
    </label>
</section>

{{-- FAQs --}}
<section class="{{ $card }}">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
            <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--help-circle] size-5 text-primary"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary">الأسئلة الشائعة</h2>
        </div>
        <button type="button" class="inline-flex items-center gap-1.5 font-semibold text-15px text-primary hover:opacity-80">
            <span class="icon-[tabler--plus] size-4"></span>
            سؤال جديد
        </button>
    </div>
    <div class="space-y-4">
        @foreach ($faqs ?? [] as $faq)
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                <div class="sm:col-span-5">
                    <input type="text" value="{{ $faq['question'] }}" class="{{ $input }}" placeholder="السؤال">
                </div>
                <div class="sm:col-span-6">
                    <input type="text" value="{{ $faq['answer'] }}" class="{{ $input }}" placeholder="الإجابة">
                </div>
                <div class="sm:col-span-1 flex sm:justify-center">
                    <button type="button" class="size-11 rounded-10px center text-red-500 hover:bg-red-50" aria-label="حذف السؤال">
                        <span class="icon-[tabler--trash] size-5"></span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- Requirements + related --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
    <section class="{{ $card }}">
        <h2 class="font-bold text-18px sm:text-20px text-primary mb-2 text-start">متطلبات يُنصح بها</h2>
        <p class="font-medium text-13px text-gray mb-4 text-start">ما الذي يجب أن يعرفه الطالب قبل البدء؟</p>
        <input type="text" class="{{ $input }} mb-4" placeholder="أضف متطلبًا ثم اضغط Enter" data-req-field>
        <div class="flex flex-wrap gap-2" data-req-list>
            @foreach ($requirements ?? [] as $req)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 font-medium text-13px text-primary">
                    {{ $req }}
                    <button type="button" class="hover:opacity-70" aria-label="حذف">
                        <span class="icon-[tabler--x] size-3.5"></span>
                    </button>
                </span>
            @endforeach
        </div>
    </section>

    <section class="{{ $card }}">
        <h2 class="font-bold text-18px sm:text-20px text-primary mb-2 text-start">دورات مقترحة ومرتبطة</h2>
        <p class="font-medium text-13px text-gray mb-4 text-start">اربط دورات ذات صلة من المنصة</p>
        <div class="relative mb-4">
            <span class="icon-[tabler--search] size-4 absolute start-3.5 top-1/2 -translate-y-1/2 text-gray"></span>
            <input type="search" class="{{ $input }} !ps-10" placeholder="ابحث عن دورة">
        </div>
        <div class="space-y-2">
            @foreach ($relatedCourses ?? [] as $course)
                <div class="flex items-center justify-between gap-3 rounded-10px border border-d9 px-3.5 py-3">
                    <p class="font-medium text-14px sm:text-15px text-primary text-start truncate">{{ $course }}</p>
                    <button type="button" class="size-8 rounded-8px center bg-primary/10 text-primary shrink-0" aria-label="إضافة">
                        <span class="icon-[tabler--plus] size-4"></span>
                    </button>
                </div>
            @endforeach
        </div>
    </section>
</div>
