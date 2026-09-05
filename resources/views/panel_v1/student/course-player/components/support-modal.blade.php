<div id="course-support-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog overlay-open:opacity-100 overlay-open:duration-300 max-w-lg">
        <div class="modal-content rounded-20px border border-d9 p-0 overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-6 pt-6 pb-2">
                <h3 class="font-bold text-32px text-black">رسالة دعم جديدة</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm" aria-label="إغلاق"
                    data-overlay="#course-support-modal">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <form class="px-6 pb-6 pt-4 space-y-7" action="#" method="POST" onsubmit="return false;">
                <div class="relative">
                    <label for="course-support-type"
                        class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                        اختر نوع الدعم
                    </label>
                    <select id="course-support-type"
                        class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                        <option selected disabled>النوع</option>
                        <option>دعم فني</option>
                        <option>دعم مالي</option>
                        <option>استفسار عام</option>
                    </select>
                </div>

                <div class="relative">
                    <label for="course-support-subject"
                        class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                        عنوان الموضوع
                    </label>
                    <input id="course-support-subject" type="text"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary"
                        placeholder="">
                </div>

                <div class="relative">
                    <label for="course-support-message"
                        class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                        الرسالة
                    </label>
                    <textarea id="course-support-message" rows="6"
                        class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-36 resize-y"
                        placeholder=""></textarea>
                </div>

                <button type="submit" class="btn btn-primary rounded-10px h-14 w-full font-bold text-20px">
                    ارسل الرسالة
                </button>
            </form>
        </div>
    </div>
</div>
