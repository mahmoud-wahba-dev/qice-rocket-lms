{{-- In-page file preview for curriculum uploads --}}
<div id="curriculum-file-preview-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="curriculum-file-preview-title"
    data-curriculum-file-preview>
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-3xl max-h-[92vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white overflow-hidden shadow-xl">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-d9">
                <h3 id="curriculum-file-preview-title" class="font-bold text-16px sm:text-18px text-primary truncate" data-preview-title>
                    معاينة الملف
                </h3>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="#" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-10px bg-primary/10 text-primary font-semibold text-13px hover:bg-primary/15 transition"
                        data-preview-open-tab>
                        <span class="icon-[tabler--external-link] size-4"></span>
                        فتح
                    </a>
                    <button type="button"
                        class="size-9 rounded-10px center hover:bg-fa transition"
                        aria-label="إغلاق"
                        data-overlay="#curriculum-file-preview-modal"
                        data-preview-close>
                        <span class="icon-[tabler--x] size-5 text-primary"></span>
                    </button>
                </div>
            </div>
            <div class="bg-[#FAFAF4] p-4 sm:p-6 min-h-[16rem] max-h-[70vh] overflow-auto center" data-preview-body>
                <p class="font-medium text-14px text-gray">لا توجد معاينة</p>
            </div>
        </div>
    </div>
</div>
