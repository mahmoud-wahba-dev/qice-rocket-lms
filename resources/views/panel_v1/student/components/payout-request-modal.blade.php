@php
    $ready = (float) ($readyPayout ?? 0);
    $min = (float) ($minimumPayout ?? 0);
    $canRequest = !empty($hasFinancialApproval) && !empty($hasSelectedBank) && $ready > 0 && ($min <= 0 || $ready >= $min);
@endphp

<div id="student-payout-request-modal" class="overlay modal overlay-open:opacity-100 modal-middle hidden" role="dialog"
    tabindex="-1" aria-labelledby="student-payout-request-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-lg max-h-[90vh] px-4 my-6">
        <div class="modal-content rounded-20px border border-d9 bg-white p-0 overflow-hidden">
            <div class="bg-primary px-6 sm:px-8 py-6 text-white">
                <h2 id="student-payout-request-modal-title" class="font-bold text-22px sm:text-24px mb-2">
                    طلب سحب الأرباح
                </h2>
                <p class="font-medium text-14px text-white/85 leading-relaxed">
                    راجع الرصيد وطريقة التحويل قبل تأكيد الطلب.
                </p>
            </div>

            <div class="px-6 sm:px-8 py-6 space-y-5">
                <div class="rounded-[12px] bg-[#F3F4F6] px-5 py-4">
                    <p class="font-medium text-13px text-gray mb-2">الرصيد الجاهز للسحب</p>
                    <p class="font-extrabold text-28px text-primary leading-none">{{ handlePrice($ready) }}</p>
                </div>

                <div class="border border-d9 rounded-[12px] px-5 py-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-medium text-13px text-gray">طريقة السحب</span>
                        <span class="font-bold text-14px text-primary text-end">
                            {{ $withdrawalMethodLabel ?? 'غير محددة بعد' }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-medium text-13px text-gray">الحد الأدنى</span>
                        <span class="font-bold text-14px text-primary text-end">
                            {{ $min > 0 ? handlePrice($min) : '—' }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-medium text-13px text-gray">حالة التوثيق المالي</span>
                        <span class="font-bold text-14px text-end {{ !empty($hasFinancialApproval) ? 'text-[#0FC787]' : 'text-[#C9A46C]' }}">
                            {{ !empty($hasFinancialApproval) ? 'موثّق' : 'بانتظار التوثيق' }}
                        </span>
                    </div>
                </div>

                @if (!$canRequest)
                    <div class="rounded-[12px] bg-[#FEF6E7] border border-[#F5D9A8]/60 px-5 py-4">
                        <p class="font-bold text-14px text-primary mb-1">لا يمكن إرسال الطلب الآن</p>
                        <p class="font-medium text-13px text-gray leading-relaxed">
                            @if (empty($hasSelectedBank))
                                أضف حساباً بنكياً من إعدادات الحساب → الهوية والمالية.
                            @elseif (empty($hasFinancialApproval))
                                انتظر اعتماد بياناتك المالية من الإدارة.
                            @elseif ($ready <= 0)
                                لا يوجد رصيد جاهز للسحب حالياً.
                            @else
                                الرصيد أقل من الحد الأدنى المطلوب للسحب.
                            @endif
                        </p>
                        <a href="{{ route('panel.v1.student.settings') }}#settings-tabs-3"
                            class="inline-flex mt-3 font-bold text-13px text-primary underline underline-offset-4">
                            فتح إعدادات الهوية والمالية
                        </a>
                    </div>
                @else
                    <p class="font-medium text-13px text-gray leading-relaxed inline-flex items-start gap-1.5">
                        <span class="icon-[tabler--bulb] size-4 text-[#C9A46C] shrink-0 mt-0.5" aria-hidden="true"></span>
                        سيتم تحويل كامل الرصيد الجاهز إلى حسابك البنكي المحدد بعد مراجعة الإدارة.
                    </p>
                @endif
            </div>

            <div class="px-6 sm:px-8 pb-6 flex flex-wrap items-center justify-end gap-3">
                <button type="button"
                    class="btn btn-ghost rounded-[10px] h-11 px-5 font-bold text-13px border border-d9"
                    data-overlay="#student-payout-request-modal">
                    إلغاء
                </button>
                @if ($canRequest)
                    <form method="POST" action="{{ route('panel.v1.student.payouts.request') }}">
                        @csrf
                        <button type="submit"
                            class="btn btn-primary rounded-[10px] h-11 px-6 font-bold text-13px border-0">
                            تأكيد طلب السحب
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
