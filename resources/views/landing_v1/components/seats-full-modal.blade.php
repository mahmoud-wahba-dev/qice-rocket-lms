{{-- "Seats are full" modal for paid course pages.
     Opened by any element with [data-seats-full-open]. Offers the waitlist when the admin enabled it.
     Expects: $course, $seatsTotal, $waitlistEnabled, $joinedWaitlist --}}
@php($isLoggedIn = auth()->check())

<div id="seats-full-modal" class="fixed inset-0 hidden items-center justify-center px-4 bg-black/50"
    style="z-index: 2147483647;" role="dialog" aria-modal="true" aria-labelledby="seats-full-title" dir="rtl">
    <div class="relative w-full max-w-[520px] rounded-20px bg-white px-8 py-10 text-center shadow-2xl">
        <button type="button" data-seats-full-close aria-label="إغلاق"
            class="absolute top-4 start-4 text-primary/50 hover:text-primary transition">
            <span class="icon-[tabler--x] size-6"></span>
        </button>

        <div class="mx-auto mb-5 flex size-20 items-center justify-center rounded-full bg-primary/10">
            <span id="seats-full-icon" class="icon-[tabler--armchair-off] size-12 text-color2"></span>
        </div>

        <h2 id="seats-full-title" class="font-bold text-24px text-primary mb-3">اكتمل عدد المقاعد</h2>

        <p id="seats-full-text" class="font-medium text-16px text-primary/80 leading-8 mb-2">
            عذراً، تم حجز جميع مقاعد هذه الدورة
            @if (!empty($seatsTotal))
                ({{ $seatsTotal }} مقعد)
            @endif
            ولا يمكن التسجيل فيها حالياً.
        </p>

        <p id="seats-full-hint" class="font-medium text-14px text-primary/60 leading-7 mb-8">
            @if (!$waitlistEnabled)
                يرجى المحاولة لاحقاً أو التواصل معنا لمعرفة موعد الدورة القادمة.
            @elseif ($joinedWaitlist)
                أنت مسجّل بالفعل في قائمة الانتظار لهذه الدورة، وسنتواصل معك عند توفر مقعد.
            @elseif ($isLoggedIn)
                يمكنك إضافة الدورة إلى قائمة الانتظار الخاصة بك، وسيصل طلبك إلى الإدارة وسنتواصل معك عند توفر مقعد.
            @else
                سجّل الدخول أولاً لتتمكن من إضافة الدورة إلى قائمة الانتظار، وسنتواصل معك عند توفر مقعد.
            @endif
        </p>

        <p id="seats-full-error" class="hidden font-medium text-14px text-[#C80A0A] mb-4"></p>

        <div class="flex flex-col gap-3">
            @if ($waitlistEnabled && !$joinedWaitlist && $isLoggedIn)
                <button type="button" id="seats-full-join" data-slug="{{ $course->slug }}"
                    class="inline-flex w-full items-center justify-center rounded-10px bg-primary px-6 py-3 font-semibold text-16px text-white hover:opacity-90 transition">
                    إضافة الدورة إلى قائمة الانتظار
                </button>
            @elseif ($waitlistEnabled && !$joinedWaitlist && !$isLoggedIn)
                <a href="/login"
                    class="inline-flex w-full items-center justify-center rounded-10px bg-primary px-6 py-3 font-semibold text-16px text-white hover:opacity-90 transition">
                    تسجيل الدخول
                </a>
            @endif
            <button type="button" data-seats-full-close
                class="inline-flex w-full items-center justify-center rounded-10px border border-primary/20 px-6 py-3 font-semibold text-16px text-primary hover:bg-primary/5 transition">
                إغلاق
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('seats-full-modal');
        if (!modal) return;

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-seats-full-open]')) {
                e.preventDefault();
                open();
            } else if (e.target === modal || e.target.closest('[data-seats-full-close]')) {
                close();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });

        var joinBtn = document.getElementById('seats-full-join');
        if (!joinBtn) return;

        joinBtn.addEventListener('click', async function () {
            var errorEl = document.getElementById('seats-full-error');
            var label = joinBtn.innerHTML;
            errorEl.classList.add('hidden');
            joinBtn.disabled = true;
            joinBtn.innerHTML = '<span class="icon-[tabler--loader-2] size-5 animate-spin inline-block"></span>';

            try {
                var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                var response = await fetch('/course/' + encodeURIComponent(joinBtn.dataset.slug) + '/waitlists/join', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                var data = await response.json().catch(function () { return {}; });

                if (response.ok && data.code === 200) {
                    document.getElementById('seats-full-title').textContent = 'تمت إضافة الدورة إلى قائمة الانتظار';
                    document.getElementById('seats-full-text').textContent = 'تم تسجيل طلبك بنجاح وسيظهر لدى الإدارة.';
                    document.getElementById('seats-full-hint').textContent = 'سنتواصل معك فور توفر مقعد في هذه الدورة.';
                    var icon = document.getElementById('seats-full-icon');
                    icon.className = 'icon-[tabler--circle-check-filled] size-12 text-green-500';
                    joinBtn.remove();
                    return;
                }

                errorEl.textContent = data.msg || 'تعذر إضافة الدورة إلى قائمة الانتظار، حاول مرة أخرى.';
                errorEl.classList.remove('hidden');
            } catch (err) {
                errorEl.textContent = 'حدث خطأ في الاتصال، حاول مرة أخرى.';
                errorEl.classList.remove('hidden');
            }

            joinBtn.disabled = false;
            joinBtn.innerHTML = label;
        });
    })();
</script>
