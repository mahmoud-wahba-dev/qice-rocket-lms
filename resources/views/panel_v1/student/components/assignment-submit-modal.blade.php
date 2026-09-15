@php
    $pending = ($pendingAssignments ?? collect())->first();
    $pendingSlug = $pending?->webinar?->slug;
    $pendingTitle = $pending?->title ?: 'تكليف معلق';
    $pendingCourse = $pending?->webinar?->title ?? '';
    $pendingDesc = strip_tags((string) ($pending?->description ?? ''));
    if ($pendingDesc === '') {
        $pendingDesc = 'اكتب إجابتك بوضوح وأرفق أي ملفات مطلوبة ثم أرسل للمدرب.';
    }
@endphp

<div id="assignment-submit-modal" class="overlay modal overlay-open:opacity-100 modal-middle hidden" role="dialog"
    tabindex="-1" aria-labelledby="assignment-submit-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[58rem] max-h-[90vh] px-4 my-6">
        <div
            class="modal-content student-assignment-modal max-h-[90vh] overflow-y-auto overscroll-contain rounded-20px border border-d9 bg-white p-8 lg:p-10">
            @if (empty($pending) || empty($pendingSlug))
                <div class="text-center py-10">
                    <p class="font-bold text-18px text-primary mb-3">لا يوجد تكليف معلق حالياً</p>
                    <button type="button" class="btn btn-ghost rounded-[10px] h-10 px-6 font-bold text-[13px] border border-[#E5E7EB]" data-overlay="#assignment-submit-modal">إغلاق</button>
                </div>
            @else
            <form method="POST"
                action="{{ route('panel.v1.student.course.assignment.submit', ['slug' => $pendingSlug]) }}"
                enctype="multipart/form-data"
                data-assignment-submit-form>
                @csrf
                <input type="hidden" name="assignment_id" value="{{ $pending->id }}">

            <div class="mb-8">
                <h2 id="assignment-submit-modal-title" class="font-bold text-32px text-[#0F172A] mb-3">
                    تقديم إجابة التكليف
                </h2>
                <p class="font-normal text-18px text-[#64748B] leading-relaxed">
                    {{ $pendingTitle }}@if($pendingCourse) — {{ $pendingCourse }}@endif
                </p>
            </div>

            <div class="bg-[#FEF6E7] border border-[#FDE68A] rounded-[12px] px-5 py-4 mb-6 flex items-start gap-3">
                <span class="size-7 rounded-full bg-[#F59E0B] text-white center shrink-0 text-[12px]">!</span>
                <div>
                    <p class="font-bold text-[13px] text-[#92400E]">تنبيه قبل التسليم</p>
                    <p class="font-medium text-[12px] text-[#B45309] leading-relaxed mt-1">تأكد من تغطية جميع النقاط المطلوبة قبل الإرسال.</p>
                </div>
            </div>
            <div class="bg-[#F9FAFB] border border-[#E5E7EB] rounded-[12px] px-5 py-4 mb-6">
                <p class="font-bold text-[13px] text-[#0F3D36] mb-2 flex items-center gap-2"><span class="icon-[tabler--file-text] size-4 text-[#0F3D36]"></span> تفاصيل التكليف:</p>
                <p class="font-medium text-[13px] text-[#374151] leading-[1.8]">{{ $pendingDesc }}</p>
            </div>

            <div class="mb-5">
                <h3 class="font-bold text-[15px] text-[#0F3D36] mb-3 flex items-center gap-2"><span class="size-6 rounded-[8px] bg-[#0F3D36] text-white center text-[11px]">1</span> إجابة التكليف والتسليم</h3>
                <label for="assignment-answer-text" class="block font-bold text-[12px] text-[#374151] mb-2">نص الإجابة <span class="font-medium text-[#9CA3AF]">(حتى 500 كلمة)</span></label>
                <div class="border border-[#E5E7EB] rounded-[12px] overflow-hidden bg-white">
                    <textarea id="assignment-answer-text" name="answer" rows="6" maxlength="3500" required
                        class="w-full resize-none border-0 bg-transparent px-4 py-3 font-medium text-[13px] text-[#111827] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-0"
                        placeholder="ابدأ كتابة نص المقال هنا..." data-assignment-word-limit="500"></textarea>
                    <div class="flex items-center justify-between px-4 py-2.5 bg-[#F9FAFB] border-t border-[#E5E7EB]">
                        <p class="font-medium text-[11px] text-[#6B7280]" data-assignment-word-count>عدد الكلمات: <span data-assignment-word-current>0</span> / <span data-assignment-word-max>500</span></p>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <p class="font-bold text-[12px] text-[#374151] mb-2">إرفاق ملف من جهازك (اختياري)</p>
                @include('panel_v1.components.file-upload', [
                    'name' => 'upload',
                    'id' => 'assignment-answer-file',
                    'accept' => '.pdf,.doc,.docx,image/*',
                    'label' => 'اضغط أو اسحب الملف للرفع',
                    'hint' => 'PDF / DOCX / صورة — حتى 10MB',
                    'required' => false,
                    'compact' => true,
                ])
            </div>

            <div class="rounded-[10px] bg-[#ECFDF5] border border-[#A7F3D0] px-4 py-3 mb-5 flex items-center gap-2">
                <span class="icon-[tabler--check] size-4 text-[#059669]"></span>
                <p class="font-medium text-[12px] text-[#065F46]">سيتم إشعار المدرب فور تسليمك، وستظهر النتيجة في تبويب التكليفات.</p>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" class="btn btn-ghost rounded-[10px] h-10 px-6 font-bold text-[13px] border border-[#E5E7EB]" data-overlay="#assignment-submit-modal">إلغاء</button>
                <button type="submit" class="btn btn-primary rounded-[10px] h-10 px-8 font-bold text-[13px] bg-[#0F3D36] border-[#0F3D36]" data-assignment-submit-btn>
                    إرسال الآن
                </button>
            </div>
            </form>
            @endif
        </div>
    </div>
</div>
