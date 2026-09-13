<div id="student-calendar-event-modal" class="overlay modal overlay-open:opacity-100 modal-middle hidden" role="dialog"
    tabindex="-1" aria-labelledby="student-calendar-event-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-lg max-h-[90vh] px-4 my-6">
        <div class="modal-content rounded-20px border border-d9 bg-white p-6 sm:p-8">
            <div class="mb-6">
                <h2 id="student-calendar-event-modal-title" class="font-bold text-24px text-primary mb-2">
                    إضافة حدث جديد
                </h2>
                <p class="font-medium text-14px text-gray">
                    أدخل عنوان الحدث للتاريخ المحدد.
                </p>
            </div>

            <form method="POST" action="{{ route('panel.v1.student.calendar-events.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="calendar-event-date" class="block font-bold text-13px text-primary mb-2">التاريخ</label>
                    <input type="date" id="calendar-event-date" name="event_date" required
                        data-calendar-event-date
                        class="input input-bordered w-full rounded-10px border-d9 h-12 font-medium text-14px">
                </div>

                <div>
                    <label for="calendar-event-title" class="block font-bold text-13px text-primary mb-2">عنوان الحدث</label>
                    <input type="text" id="calendar-event-title" name="title" required maxlength="255"
                        data-calendar-event-title
                        placeholder="مثال: مراجعة الوحدة الأولى"
                        class="input input-bordered w-full rounded-10px border-d9 h-12 font-medium text-14px">
                </div>

                <div>
                    <label for="calendar-event-notes" class="block font-bold text-13px text-primary mb-2">
                        ملاحظات <span class="font-medium text-gray">(اختياري)</span>
                    </label>
                    <textarea id="calendar-event-notes" name="notes" rows="3" maxlength="1000"
                        data-calendar-event-notes
                        placeholder="تفاصيل إضافية..."
                        class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-14px resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" class="btn btn-ghost rounded-10px h-11 px-5 font-bold text-13px border border-d9"
                        data-overlay="#student-calendar-event-modal">
                        إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary rounded-10px h-11 px-6 font-bold text-13px">
                        حفظ الحدث
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<button type="button" id="student-calendar-event-modal-trigger" class="hidden"
    data-overlay="#student-calendar-event-modal" aria-hidden="true"></button>
