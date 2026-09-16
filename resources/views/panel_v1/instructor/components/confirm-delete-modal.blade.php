{{-- Shared confirm-delete modal (panel_v1 instructor color schema) --}}
<div id="instructor-confirm-delete-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-confirm-delete-title"
    data-instructor-confirm-delete>
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-md max-h-[90vh] px-4 my-6">
        <form id="instructor-confirm-delete-form" method="POST" action="#"
            class="modal-content relative rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl">
            @csrf

            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3 z-10"
                aria-label="إغلاق"
                data-overlay="#instructor-confirm-delete-modal">
                <span class="icon-[tabler--x] size-5 text-primary"></span>
            </button>

            <div class="px-6 sm:px-8 pt-10 pb-8 text-center">
                <span class="size-16 rounded-full bg-[#FEF2F2] border border-[#FECACA] center mx-auto mb-5">
                    <span class="icon-[tabler--trash] size-8 text-[#EF4444]"></span>
                </span>

                <h3 id="instructor-confirm-delete-title" class="font-bold text-22px text-primary mb-2">
                    تأكيد الحذف
                </h3>
                <p id="instructor-confirm-delete-message"
                    class="font-medium text-15px text-gray leading-relaxed mb-3 max-w-sm mx-auto">
                    هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع بعد الحذف.
                </p>
                <p id="instructor-confirm-delete-item"
                    class="hidden font-semibold text-14px text-primary bg-fa border border-d9 rounded-12px px-4 py-3 mb-6 line-clamp-2"></p>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-center gap-3 mt-6">
                    <button type="button"
                        class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-primary border border-d9 hover:bg-fa"
                        data-overlay="#instructor-confirm-delete-modal">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="btn rounded-12px h-12 px-6 font-bold text-15px bg-[#EF4444] hover:bg-[#DC2626] text-white border-0">
                        حذف نهائياً
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
