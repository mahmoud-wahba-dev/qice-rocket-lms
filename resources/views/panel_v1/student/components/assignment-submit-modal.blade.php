<div id="assignment-submit-modal" class="overlay modal overlay-open:opacity-100 modal-middle hidden" role="dialog"
    tabindex="-1" aria-labelledby="assignment-submit-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[58rem] max-h-[90vh] px-4 my-6">
        <div
            class="modal-content student-assignment-modal max-h-[90vh] overflow-y-auto overscroll-contain rounded-20px border border-d9 bg-white p-8 lg:p-10">
            <div class="mb-8">
                <h2 id="assignment-submit-modal-title" class="font-bold text-32px text-[#0F172A] mb-3">
                    تقديم إجابة التكليف
                </h2>
                <p class="font-normal text-18px text-[#64748B] leading-relaxed">
                    اكتب نص الإجابة أو أرفق الملفات المطلوبة لتسليم التكليف للمحاضر.
                </p>
            </div>

            <div
                class="student-assignment-modal__details bg-[#F8FAFC] border border-[#E2E8F0] rounded-12px px-6 py-5 mb-7">
                <p class="font-bold text-16px text-[#0F172A] mb-1">
                    📝 تفاصيل التكليف والمحتوى المطلوب:
                </p>
                <p class="font-normal text-16px text-[#334155] leading-[1.9] mb-1">
                    اكتب مقالاً تحليلياً متكاملاً يتناول دور أخصائي الجودة في تقليل الأخطاء الطبية، مع التركيز على
                    أهمية قياس مؤشرات الأداء (KPIs) في تحسين جودة الرعاية الصحية.
                </p>
                <p class="font-bold text-20px text-[#334155] mb-2">
                    📌 نقاط يجب تغطيتها في المقال:
                </p>
                <p class="font-normal text-16px text-[#475569] leading-[1.9]">
                    • مقدمة عن مفهوم سلامة المرضى • شرح أدوات قياس الأداء (KPIs) • خاتمة تتضمن توصيات للتطبيق العملي.
                </p>
            </div>

            <div class="mb-6">
                <h3 class="font-bold text-20px text-[#0F172A] mb-3">إجابة التكليف والتسليم</h3>

                <label for="assignment-answer-text" class="block font-medium text-18px text-[#0F172A] mb-2">
                    كتابة وصف
                </label>

                <div class="student-assignment-modal__editor border border-d9 rounded-12px overflow-hidden bg-white">
                    <div class="student-assignment-modal__editor-bar bg-[#F1F5F9] border-b border-d9 min-h-10"></div>
                    <textarea id="assignment-answer-text" name="answer" rows="" maxlength="3500"
                        class="student-assignment-modal__textarea w-full h-full resize-none border-0 bg-transparent px-5 py-4 font-normal text-16px text-black placeholder:text-gray focus:outline-none focus:ring-0"
                        placeholder="ابدأ كتابة نص المقال هنا..." data-assignment-word-limit="500"></textarea>
                    <div class="px-5 pb-4 pt-1">
                        <p class="font-normal text-14px text-gray text-start" data-assignment-word-count>
                            عدد الكلمات: <span data-assignment-word-current>0</span> / <span
                                data-assignment-word-max>500</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <p class="font-medium text-16px text-gray mb-3 ">
                    إرفاق المقال كملف خارجي (اختياري)
                </p>

                <label for="assignment-answer-fil"
                    class="student-assignment-modal__upload center  border-[#0D9488] flex-col gap-2 rounded-12px px-6 py-8 cursor-pointer transition-colors hover:bg-primary/10">
                    <input id="assignment-answer-file" type="file" name="attachment" accept=".pdf,.doc,.docx"
                        class="sr-only" data-assignment-file-input>
                    <span class="font-bold text-16px text-[#0D9488] text-center leading-relaxed"
                        data-assignment-file-label>
                        📎 اضغط هنا لرفع الملف بصيغة (PDF أو DOCX)
                    </span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="button" class="btn btn-primary rounded-8px px-12 h-12 font-bold text-16px min-w-[9.5rem]">
                    ارسل الآن
                </button>
            </div>
        </div>
    </div>
</div>
