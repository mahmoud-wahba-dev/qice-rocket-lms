{{-- Add new coupon modal --}}
<div id="instructor-coupon-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-coupon-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[40%] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white p-6 sm:p-8 overflow-y-auto overflow-x-hidden overscroll-contain shadow-xl max-h-[90vh]">
            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3"
                aria-label="إغلاق"
                data-overlay="#instructor-coupon-modal">
                <span class="icon-[tabler--x] size-5 text-gray"></span>
            </button>

            <h3 id="instructor-coupon-modal-title" class="font-bold text-22px sm:text-24px text-primary mb-6 pe-8 text-start">
                إضافة قسيمة جديدة
            </h3>

            <form class="space-y-5 text-start" onsubmit="return false;">
                <div>
                    <label for="coupon-name" class="block font-semibold text-15px sm:text-16px text-primary mb-2">الاسم</label>
                    <select id="coupon-name"
                        class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 bg-white font-medium text-15px sm:text-16px text-primary">
                        <option value="" disabled selected>اختر الدورة اولا</option>
                        <option value="demo">دبلومة UI/UX الشاملة</option>
                        <option value="cphq">الممارس المعتمد CPHQ</option>
                    </select>
                </div>

                <div>
                    <label for="coupon-student" class="block font-semibold text-15px sm:text-16px text-primary mb-2">
                        البريد الإلكتروني أو الهاتف:
                    </label>
                    <select id="coupon-student"
                        class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 bg-white font-medium text-15px sm:text-16px text-primary">
                        <option value="" disabled selected>اختر الطالب</option>
                        <option value="1">علا محمد</option>
                        <option value="2">أحمد خالد</option>
                    </select>
                </div>

                <div>
                    <label for="coupon-password" class="block font-semibold text-15px sm:text-16px text-primary mb-2">كلمة المرور</label>
                    <input id="coupon-password" type="password" placeholder="********"
                        class="input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-primary placeholder:text-gray">
                </div>

                <div>
                    <label for="coupon-role" class="block font-semibold text-15px sm:text-16px text-primary mb-2">دور المستخدم</label>
                    <select id="coupon-role"
                        class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 bg-white font-medium text-15px sm:text-16px text-primary mb-3">
                        <option value="" disabled selected>اختر دور المستخدم</option>
                        <option value="student">طالب</option>
                        <option value="instructor">مدرب</option>
                    </select>
                    <select id="coupon-role-2"
                        class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 bg-white font-medium text-15px sm:text-16px text-primary"
                        aria-label="دور المستخدم الإضافي">
                        <option value="" disabled selected>اختر دور المستخدم</option>
                        <option value="student">طالب</option>
                        <option value="instructor">مدرب</option>
                    </select>
                </div>

                <div>
                    <p class="font-semibold text-15px sm:text-16px text-primary mb-2">مجموعة</p>
                    <label for="coupon-status" class="block font-semibold text-15px sm:text-16px text-primary mb-2">الحالة</label>
                    <select id="coupon-status"
                        class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 bg-white font-medium text-15px sm:text-16px text-primary">
                        <option value="" disabled selected>اختر دور المستخدم</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-12px bg-primary h-12 sm:h-14 font-bold text-16px sm:text-18px text-white hover:opacity-95 transition mt-2"
                    data-overlay="#instructor-coupon-modal">
                    حفظ المستخدم
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Add new discount modal --}}
<div id="instructor-discount-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-discount-modal-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[40%] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative rounded-20px border border-d9 bg-white p-6 sm:p-8 overflow-y-auto overflow-x-hidden overscroll-contain shadow-xl max-h-[90vh]">
            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3"
                aria-label="إغلاق"
                data-overlay="#instructor-discount-modal">
                <span class="icon-[tabler--x] size-5 text-gray"></span>
            </button>

            <h3 id="instructor-discount-modal-title" class="font-bold text-22px sm:text-24px text-primary mb-6 pe-8 text-start">
                إضافة خصم جديد
            </h3>

            <form method="POST" action="{{ route('panel.v1.instructor.discounts.store') }}" class="space-y-5 text-start">
                @csrf
                <div>
                    <label for="discount-title" class="block font-semibold text-15px sm:text-16px text-primary mb-2">العنوان</label>
                    <input id="discount-title" name="title" type="text" required maxlength="255" placeholder="اضف عنوان"
                        class="input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-primary placeholder:text-gray">
                </div>

                <div>
                    <label for="discount-percent" class="block font-semibold text-15px sm:text-16px text-primary mb-2">نسبة الخصم (1-100)</label>
                    <input id="discount-percent" name="percent" type="number" min="1" max="100" required placeholder=""
                        class="input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-primary">
                    <p class="font-medium text-13px sm:text-14px text-primary mt-2.5 leading-relaxed">
                        سيتم توليد كود خصم تلقائيًا صالحًا لمدة 30 يومًا
                    </p>
                </div>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-12px bg-primary h-12 sm:h-14 font-bold text-16px sm:text-18px text-white hover:opacity-95 transition mt-2">
                    انشاء الخصم
                </button>
            </form>
        </div>
    </div>
</div>
