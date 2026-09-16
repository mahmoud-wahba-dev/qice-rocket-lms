{{-- Create coupon --}}
<div id="instructor-discount-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-discount-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[40%] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white p-6 sm:p-8 overflow-y-auto overflow-x-hidden overscroll-contain shadow-xl max-h-[90vh]">
            <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="إغلاق"
                data-overlay="#instructor-discount-modal">
                <span class="icon-[tabler--x] size-5 text-gray"></span>
            </button>

            <h3 id="instructor-discount-modal-title" class="font-bold text-22px sm:text-24px text-primary mb-6 pe-8 text-start">
                إنشاء قسيمة خصم جديدة
            </h3>

            <form method="POST" action="{{ route('panel.v1.instructor.discounts.store') }}" class="space-y-5 text-start">
                @csrf
                <div>
                    <label for="discount-title" class="block font-semibold text-15px text-primary mb-2">عنوان القسيمة</label>
                    <input id="discount-title" name="title" type="text" required maxlength="255" placeholder="مثال: خصم رمضان"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                </div>
                <div>
                    <label for="discount-percent" class="block font-semibold text-15px text-primary mb-2">نسبة الخصم %</label>
                    <input id="discount-percent" name="percent" type="number" min="1" max="100" required value="15"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                </div>
                <div>
                    <label for="discount-course" class="block font-semibold text-15px text-primary mb-2">الدورة (اختياري)</label>
                    <select id="discount-course" name="webinar_id"
                        class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                        <option value="">كل الدورات</option>
                        @foreach ($courseOptions ?? [] as $course)
                            <option value="{{ $course['id'] }}">{{ $course['title'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="discount-count" class="block font-semibold text-15px text-primary mb-2">عدد الاستخدامات</label>
                        <input id="discount-count" name="count" type="number" min="1" max="10000" value="100"
                            class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                    </div>
                    <div>
                        <label for="discount-days" class="block font-semibold text-15px text-primary mb-2">الصلاحية (أيام)</label>
                        <input id="discount-days" name="days" type="number" min="1" max="365" value="30"
                            class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                    </div>
                </div>
                <p class="font-medium text-13px text-gray">سيتم توليد كود الخصم تلقائياً.</p>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-12px bg-primary h-12 font-bold text-16px text-white hover:opacity-95 transition">
                    إنشاء القسيمة
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Create course special offer --}}
<div id="instructor-offer-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-offer-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[40%] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white p-6 sm:p-8 overflow-y-auto overflow-x-hidden overscroll-contain shadow-xl max-h-[90vh]">
            <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="إغلاق"
                data-overlay="#instructor-offer-modal">
                <span class="icon-[tabler--x] size-5 text-gray"></span>
            </button>

            <h3 id="instructor-offer-modal-title" class="font-bold text-22px sm:text-24px text-primary mb-6 pe-8 text-start">
                إنشاء تخفيض لدورة
            </h3>

            <form method="POST" action="{{ route('panel.v1.instructor.special-offers.store') }}" class="space-y-5 text-start">
                @csrf
                <div>
                    <label for="offer-title" class="block font-semibold text-15px text-primary mb-2">عنوان التخفيض</label>
                    <input id="offer-title" name="title" type="text" required maxlength="255" placeholder="مثال: عرض الأسبوع"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                </div>
                <div>
                    <label for="offer-course" class="block font-semibold text-15px text-primary mb-2">الدورة</label>
                    <select id="offer-course" name="webinar_id" required
                        class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                        <option value="" disabled selected>اختر الدورة</option>
                        @foreach ($courseOptions ?? [] as $course)
                            <option value="{{ $course['id'] }}">{{ $course['title'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="offer-percent" class="block font-semibold text-15px text-primary mb-2">نسبة التخفيض %</label>
                    <input id="offer-percent" name="percent" type="number" min="1" max="100" required value="20"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="offer-from" class="block font-semibold text-15px text-primary mb-2">من تاريخ</label>
                        <input id="offer-from" name="from_date" type="date" required value="{{ date('Y-m-d') }}"
                            class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-14px text-primary">
                    </div>
                    <div>
                        <label for="offer-to" class="block font-semibold text-15px text-primary mb-2">إلى تاريخ</label>
                        <input id="offer-to" name="to_date" type="date" required value="{{ date('Y-m-d', strtotime('+14 days')) }}"
                            class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-14px text-primary">
                    </div>
                </div>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-12px bg-primary h-12 font-bold text-16px text-white hover:opacity-95 transition">
                    إنشاء التخفيض
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Request promotional plan --}}
<div id="instructor-promo-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-promo-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[40%] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white p-6 sm:p-8 overflow-y-auto overflow-x-hidden overscroll-contain shadow-xl max-h-[90vh]">
            <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="إغلاق"
                data-overlay="#instructor-promo-modal">
                <span class="icon-[tabler--x] size-5 text-gray"></span>
            </button>

            <h3 id="instructor-promo-modal-title" class="font-bold text-22px sm:text-24px text-primary mb-2 pe-8 text-start">
                طلب خطة ترويجية
            </h3>
            <p class="font-medium text-14px text-gray mb-6 text-start">سيتم فتح طلب جديد وتوجيهه لفريق التسويق.</p>

            <form method="POST" action="{{ route('panel.v1.instructor.promotions.request') }}" class="space-y-5 text-start">
                @csrf
                <div>
                    <label for="promo-plan" class="block font-semibold text-15px text-primary mb-2">الخطة الترويجية</label>
                    <select id="promo-plan" name="promotion_id" required
                        class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                        <option value="" disabled selected>اختر الخطة</option>
                        @foreach ($promotionPlans ?? [] as $plan)
                            <option value="{{ $plan['id'] }}">
                                {{ $plan['title'] }} — {{ $plan['price'] }} / {{ $plan['days'] }} يوم
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="promo-course" class="block font-semibold text-15px text-primary mb-2">الدورة المراد ترويجها</label>
                    <select id="promo-course" name="webinar_id" required
                        class="select select-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                        <option value="" disabled selected>اختر الدورة</option>
                        @foreach ($courseOptions ?? [] as $course)
                            <option value="{{ $course['id'] }}">{{ $course['title'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="promo-message" class="block font-semibold text-15px text-primary mb-2">ملاحظة (اختياري)</label>
                    <textarea id="promo-message" name="message" rows="3" maxlength="2000"
                        class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px text-primary"
                        placeholder="أضف تفاصيل إضافية للفريق..."></textarea>
                </div>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-12px bg-primary h-12 font-bold text-16px text-white hover:opacity-95 transition">
                    إرسال الطلب للفريق المختص
                </button>
            </form>
        </div>
    </div>
</div>
