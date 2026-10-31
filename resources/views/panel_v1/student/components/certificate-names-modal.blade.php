@php
    $prefillAr = $traineeNameArPrefill ?? (auth()->user()->full_name ?? '');
    $prefillEn = $traineeNameEnPrefill ?? '';
    $initialCertId = $certificateId ?? ($openCertNamesId ?? null);
    $needsNames = !empty($certificateNeedsNames) || !empty($openCertNamesId);
    $completeUrlTemplate = route('panel.v1.student.certificates.complete-names', ['id' => 0]);
    $completeUrlTemplate = preg_replace('#/0/complete-names$#', '/__ID__/complete-names', $completeUrlTemplate);
    // Real POST target so a missing JS build never POSTs to the current /watch page (405).
    $completeUrl = !empty($initialCertId)
        ? route('panel.v1.student.certificates.complete-names', ['id' => (int) $initialCertId])
        : url('/v1/student/certificates');
@endphp

<div id="student-certificate-names-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="student-certificate-names-title"
    data-certificate-names-modal
    data-complete-url-template="{{ $completeUrlTemplate }}"
    data-auto-open="{{ $needsNames && !empty($initialCertId) ? '1' : '0' }}"
    data-initial-certificate-id="{{ $initialCertId ?: '' }}">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-md max-h-[90vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl">
            <button type="button"
                class="absolute top-4 end-4 bg-primary btn btn-sm btn-circle btn-ghost text-white/90 hover:bg-white/10 z-10"
                aria-label="إغلاق" data-overlay="#student-certificate-names-modal">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <div class="bg-primary px-6 sm:px-8 py-6 text-white">
                <h2 id="student-certificate-names-title" class="font-bold text-22px sm:text-24px mb-2">
                    بيانات الشهادة
                </h2>
                <p class="font-medium text-14px text-white/85 leading-relaxed">
                    أدخل اسمك كما تريد ظهوره على الشهادة بالعربية والإنجليزية.
                </p>
            </div>

            <form method="POST" action="{{ $completeUrl }}" class="px-6 sm:px-8 py-6 space-y-5" data-certificate-names-form>
                @csrf
                <input type="hidden" name="view" value="0" data-certificate-names-view>
                <input type="hidden" name="certificate_id" value="{{ $initialCertId ?: '' }}" data-certificate-names-id>

                <div>
                    <label class="block font-semibold text-14px text-primary mb-2 text-start">الاسم بالعربية</label>
                    <input type="text" name="trainee_name_ar" required maxlength="255" dir="rtl"
                        value="{{ $prefillAr }}"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-black focus:outline-none focus:border-primary"
                        placeholder="مثال: محمد أحمد العتيبي"
                        data-certificate-names-ar>
                </div>

                <div>
                    <label class="block font-semibold text-14px text-primary mb-2 text-start">Name in English</label>
                    <input type="text" name="trainee_name_en" required maxlength="255" dir="ltr"
                        value="{{ $prefillEn }}"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-black focus:outline-none focus:border-primary"
                        placeholder="e.g. Mohammed Ahmed Alotaibi"
                        data-certificate-names-en>
                </div>

                <p class="hidden font-medium text-13px text-[#B91C1C]" data-certificate-names-error></p>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button"
                        class="btn btn-ghost rounded-10px h-11 px-5 font-bold text-14px border border-d9"
                        data-overlay="#student-certificate-names-modal">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-10px h-11 px-6 font-bold text-14px"
                        data-certificate-names-submit>
                        حفظ وتنزيل الشهادة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
