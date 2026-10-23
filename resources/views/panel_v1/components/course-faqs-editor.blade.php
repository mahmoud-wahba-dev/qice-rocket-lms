{{--
  Optional FAQ repeater for course wizard / forms.
  @param array $faqs  [{id?, title, answer}, ...]
  @param string $card  optional outer card classes
--}}
@php
    $faqs = old('faqs', $faqs ?? []);
    if (!is_array($faqs)) {
        $faqs = [];
    }
    $card = $card ?? 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $input = 'input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-14px sm:text-15px text-black focus:outline-none focus:border-primary';
    $textarea = 'textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-14px sm:text-15px text-black focus:outline-none focus:border-primary min-h-24 resize-y';
@endphp

<section class="{{ $card }}" data-course-faqs>
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div class="flex items-start gap-3 min-w-0">
            <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--help-circle] size-5 text-primary"></span>
            </span>
            <div class="text-start min-w-0">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">الأسئلة الشائعة</h2>
                <p class="font-medium text-14px text-gray">اختياري — تظهر في صفحة تفاصيل الدورة قبل قسم الدفع</p>
            </div>
        </div>
        <button type="button"
            class="inline-flex items-center justify-center gap-2 h-11 px-4 rounded-12px border border-primary bg-white font-bold text-14px text-primary hover:bg-primary hover:text-white transition shrink-0"
            data-faq-add>
            <span class="icon-[tabler--plus] size-4"></span>
            إضافة سؤال
        </button>
    </div>

    <div class="space-y-4" data-faq-list>
        @forelse ($faqs as $index => $faq)
            <div class="rounded-12px border border-d9 bg-[#FAFAF4] p-4 sm:p-5 space-y-3" data-faq-row>
                <input type="hidden" name="faqs[{{ $index }}][id]" value="{{ $faq['id'] ?? '' }}">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold text-13px text-gray">سؤال {{ $index + 1 }}</p>
                    <button type="button" class="size-9 rounded-10px border border-red-200 bg-white center hover:bg-[#FEF2F2] transition" data-faq-remove title="حذف">
                        <span class="icon-[tabler--trash] size-4 text-[#DC2626]"></span>
                    </button>
                </div>
                <div>
                    <label class="block font-semibold text-13px text-primary mb-1.5">السؤال</label>
                    <input type="text" name="faqs[{{ $index }}][title]" value="{{ $faq['title'] ?? '' }}"
                        class="{{ $input }}" placeholder="مثال: هل الشهادة معتمدة؟" maxlength="255">
                </div>
                <div>
                    <label class="block font-semibold text-13px text-primary mb-1.5">الإجابة</label>
                    <textarea name="faqs[{{ $index }}][answer]" rows="3" class="{{ $textarea }}"
                        placeholder="اكتب الإجابة هنا...">{{ $faq['answer'] ?? '' }}</textarea>
                </div>
            </div>
        @empty
            <div class="rounded-12px border border-dashed border-d9 bg-[#FAFAF4] px-4 py-8 text-center" data-faq-empty>
                <p class="font-medium text-14px text-gray">لا توجد أسئلة بعد — اضغط «إضافة سؤال» إن رغبت.</p>
            </div>
        @endforelse
    </div>
</section>

<template id="course-faq-row-template">
    <div class="rounded-12px border border-d9 bg-[#FAFAF4] p-4 sm:p-5 space-y-3" data-faq-row>
        <input type="hidden" name="faqs[__INDEX__][id]" value="">
        <div class="flex items-start justify-between gap-3">
            <p class="font-semibold text-13px text-gray" data-faq-label>سؤال</p>
            <button type="button" class="size-9 rounded-10px border border-red-200 bg-white center hover:bg-[#FEF2F2] transition" data-faq-remove title="حذف">
                <span class="icon-[tabler--trash] size-4 text-[#DC2626]"></span>
            </button>
        </div>
        <div>
            <label class="block font-semibold text-13px text-primary mb-1.5">السؤال</label>
            <input type="text" name="faqs[__INDEX__][title]" value=""
                class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-14px sm:text-15px text-black focus:outline-none focus:border-primary"
                placeholder="مثال: هل الشهادة معتمدة؟" maxlength="255">
        </div>
        <div>
            <label class="block font-semibold text-13px text-primary mb-1.5">الإجابة</label>
            <textarea name="faqs[__INDEX__][answer]" rows="3"
                class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-14px sm:text-15px text-black focus:outline-none focus:border-primary min-h-24 resize-y"
                placeholder="اكتب الإجابة هنا..."></textarea>
        </div>
    </div>
</template>

<script>
(() => {
    const root = document.querySelector('[data-course-faqs]');
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    const list = root.querySelector('[data-faq-list]');
    const addBtn = root.querySelector('[data-faq-add]');
    const tpl = document.getElementById('course-faq-row-template');
    if (!list || !addBtn || !tpl) return;

    const reindex = () => {
        list.querySelectorAll('[data-faq-row]').forEach((row, index) => {
            row.querySelectorAll('[name^="faqs["]').forEach((el) => {
                el.name = el.name.replace(/faqs\[\d+\]/, 'faqs[' + index + ']');
            });
            const label = row.querySelector('[data-faq-label]');
            if (label) label.textContent = 'سؤال ' + (index + 1);
            else {
                const p = row.querySelector('.font-semibold.text-13px.text-gray');
                if (p) p.textContent = 'سؤال ' + (index + 1);
            }
        });
        const empty = list.querySelector('[data-faq-empty]');
        if (empty) empty.classList.toggle('hidden', list.querySelectorAll('[data-faq-row]').length > 0);
    };

    addBtn.addEventListener('click', () => {
        const empty = list.querySelector('[data-faq-empty]');
        if (empty) empty.remove();
        const index = list.querySelectorAll('[data-faq-row]').length;
        const html = tpl.innerHTML.replace(/__INDEX__/g, String(index));
        list.insertAdjacentHTML('beforeend', html);
        reindex();
    });

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-faq-remove]');
        if (!btn) return;
        const row = btn.closest('[data-faq-row]');
        if (row) row.remove();
        if (!list.querySelector('[data-faq-row]')) {
            list.innerHTML = '<div class="rounded-12px border border-dashed border-d9 bg-[#FAFAF4] px-4 py-8 text-center" data-faq-empty><p class="font-medium text-14px text-gray">لا توجد أسئلة بعد — اضغط «إضافة سؤال» إن رغبت.</p></div>';
        } else {
            reindex();
        }
    });
})();
</script>
