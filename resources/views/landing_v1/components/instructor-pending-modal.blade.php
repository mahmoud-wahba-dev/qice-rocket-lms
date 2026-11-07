{{-- Shown once after instructor registration / login while the account awaits admin approval.
     Triggered by session('instructor_pending_modal') = 'registered' | 'login'. --}}
@php
    $pendingModal = session('instructor_pending_modal');
    $isRegistered = $pendingModal === 'registered';
@endphp

@if (!empty($pendingModal))
    <div id="instructor-pending-modal" class="fixed inset-0 flex items-center justify-center px-4 bg-black/50"
        style="z-index: 2147483647;" role="dialog" aria-modal="true" aria-labelledby="instructor-pending-title" dir="rtl">
        <div class="relative w-full max-w-[520px] rounded-20px bg-white px-8 py-10 text-center shadow-2xl">
            <button type="button" data-instructor-pending-close aria-label="إغلاق"
                class="absolute top-4 start-4 text-primary/50 hover:text-primary transition">
                <span class="icon-[tabler--x] size-6"></span>
            </button>

            <div class="mx-auto mb-5 flex size-20 items-center justify-center rounded-full bg-primary/10">
                @if ($isRegistered)
                    <span class="icon-[tabler--circle-check-filled] size-12 text-green-500"></span>
                @else
                    <span class="icon-[tabler--clock-hour-4] size-12 text-color2"></span>
                @endif
            </div>

            <h2 id="instructor-pending-title" class="font-bold text-24px text-primary mb-3">
                {{ $isRegistered ? 'تم التسجيل كمدرب بنجاح' : 'حسابك قيد المراجعة' }}
            </h2>

            <p class="font-medium text-16px text-primary/80 leading-8 mb-2">
                @if ($isRegistered)
                    تم إنشاء حسابك كمدرب، وهو الآن بانتظار موافقة الإدارة.
                @else
                    حسابك كمدرب لم يتم تفعيله بعد، وهو بانتظار موافقة الإدارة.
                @endif
            </p>
            <p class="font-medium text-14px text-primary/60 leading-7 mb-8">
                سيتم تفعيل حسابك بعد المراجعة، ويمكنك بعدها تسجيل الدخول والبدء في إنشاء دوراتك.
            </p>

            <button type="button" data-instructor-pending-close
                class="inline-flex w-full items-center justify-center rounded-10px bg-primary px-6 py-3 font-semibold text-16px text-white hover:opacity-90 transition">
                حسناً، فهمت
            </button>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('instructor-pending-modal');
            if (!modal) return;

            function closeModal() {
                modal.remove();
                document.body.style.overflow = '';
                document.removeEventListener('keydown', onKey);
            }

            function onKey(e) {
                if (e.key === 'Escape') closeModal();
            }

            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', onKey);
            modal.addEventListener('click', function (e) {
                if (e.target === modal || e.target.closest('[data-instructor-pending-close]')) {
                    closeModal();
                }
            });
        })();
    </script>
@endif
