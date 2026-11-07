{{--
  Reusable confirmation modal (replaces the browser's confirm()). Included once per page by
  components/v1/flash.blade.php, so every layout already has it. NEVER use window.confirm/alert/prompt.

  1) Declarative — put data-confirm on a <form> (confirms on submit) or on a button/link (confirms on click):
       <form method="POST" action="..." data-confirm="حذف هذا العنصر؟" data-confirm-title="حذف العنصر" data-confirm-ok="حذف" data-confirm-tone="danger">
       <a href="..." data-confirm="...">
     Optional: data-confirm-title, data-confirm-ok (button label), data-confirm-cancel, data-confirm-tone = danger|warning|primary
     (tone defaults to "danger" when the message mentions حذف/إزالة/رفض, otherwise "primary").

  2) From JS (returns a Promise<boolean>):
       if (await window.confirmModal({ title: '...', message: '...', ok: 'حذف', tone: 'danger' })) { ... }

  Messages for the user (no confirmation needed) use the toast: window.showCartToast(title, msg, 'success'|'error').
--}}
@once
    <div id="v1-confirm-modal" class="fixed inset-0 hidden items-center justify-center px-4 bg-black/50"
        style="z-index: 2147483647;" role="dialog" aria-modal="true" aria-labelledby="v1-confirm-title" dir="rtl">
        <div class="w-full max-w-[460px] rounded-20px bg-white px-8 py-9 text-center shadow-2xl">
            <div id="v1-confirm-icon-wrap" class="mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-primary/10">
                <span id="v1-confirm-icon" class="icon-[tabler--help-circle] size-9 text-primary"></span>
            </div>

            <h2 id="v1-confirm-title" class="font-bold text-22px text-primary mb-2">تأكيد الإجراء</h2>
            <p id="v1-confirm-message" class="font-medium text-16px text-primary/70 leading-8 mb-8"></p>

            <div class="flex flex-col-reverse sm:flex-row-reverse gap-3">
                <button type="button" id="v1-confirm-ok" data-v1-confirm-ok
                    class="inline-flex flex-1 items-center justify-center rounded-10px px-6 py-3 font-semibold text-16px text-white bg-primary hover:opacity-90 transition">
                    تأكيد
                </button>
                <button type="button" data-v1-confirm-cancel
                    class="inline-flex flex-1 items-center justify-center rounded-10px border border-primary/20 px-6 py-3 font-semibold text-16px text-primary hover:bg-primary/5 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            if (window.confirmModal) return;

            var modal = document.getElementById('v1-confirm-modal');
            if (!modal) return;

            // Full class strings are written out so Tailwind/iconify generate them (do not build them dynamically).
            var TONES = {
                primary: {
                    wrap: 'mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-primary/10',
                    icon: 'icon-[tabler--help-circle] size-9 text-primary',
                    ok: 'inline-flex flex-1 items-center justify-center rounded-10px px-6 py-3 font-semibold text-16px text-white bg-primary hover:opacity-90 transition'
                },
                warning: {
                    wrap: 'mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-[#FFFBEB]',
                    icon: 'icon-[tabler--archive] size-9 text-[#D97706]',
                    ok: 'inline-flex flex-1 items-center justify-center rounded-10px px-6 py-3 font-semibold text-16px text-white bg-[#D97706] hover:opacity-90 transition'
                },
                danger: {
                    wrap: 'mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-[#FEF2F2]',
                    icon: 'icon-[tabler--alert-triangle] size-9 text-[#DC2626]',
                    ok: 'inline-flex flex-1 items-center justify-center rounded-10px px-6 py-3 font-semibold text-16px text-white bg-[#DC2626] hover:opacity-90 transition'
                }
            };

            var els = {
                wrap: document.getElementById('v1-confirm-icon-wrap'),
                icon: document.getElementById('v1-confirm-icon'),
                title: document.getElementById('v1-confirm-title'),
                message: document.getElementById('v1-confirm-message'),
                ok: document.getElementById('v1-confirm-ok'),
                cancel: modal.querySelector('[data-v1-confirm-cancel]')
            };
            var pending = null;
            var lastFocus = null;

            function pickTone(message, tone) {
                if (tone && TONES[tone]) return tone;
                return /حذف|إزالة|رفض/.test(message || '') ? 'danger' : 'primary';
            }

            function finish(result) {
                if (!pending) return;
                var done = pending;
                pending = null;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
                document.removeEventListener('keydown', onKey);
                if (lastFocus && lastFocus.focus) lastFocus.focus();
                done(result);
            }

            function onKey(e) {
                if (e.key === 'Escape') finish(false);
            }

            window.confirmModal = function (opts) {
                opts = opts || {};
                if (pending) finish(false);

                var tone = TONES[pickTone(opts.message, opts.tone)];
                els.wrap.className = tone.wrap;
                els.icon.className = tone.icon;
                els.ok.className = tone.ok;
                els.title.textContent = opts.title || 'تأكيد الإجراء';
                els.message.textContent = opts.message || 'هل أنت متأكد؟';
                els.ok.textContent = opts.ok || 'تأكيد';
                els.cancel.textContent = opts.cancel || 'إلغاء';

                lastFocus = document.activeElement;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                document.addEventListener('keydown', onKey);
                els.cancel.focus();

                return new Promise(function (resolve) { pending = resolve; });
            };

            modal.addEventListener('click', function (e) {
                if (e.target === modal || e.target.closest('[data-v1-confirm-cancel]')) {
                    finish(false);
                } else if (e.target.closest('[data-v1-confirm-ok]')) {
                    finish(true);
                }
            });

            function optionsFrom(el) {
                var d = el.dataset;
                return {
                    message: d.confirm,
                    title: d.confirmTitle,
                    ok: d.confirmOk,
                    cancel: d.confirmCancel,
                    tone: d.confirmTone
                };
            }

            // <form data-confirm="..."> — ask on submit, then submit for real.
            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || !form.hasAttribute || !form.hasAttribute('data-confirm')) return;
                if (form.__v1Confirmed) {
                    form.__v1Confirmed = false;
                    return;
                }
                // Stop the event entirely so no other submit handler (e.g. AJAX) runs before the user confirms.
                e.preventDefault();
                e.stopImmediatePropagation();
                var submitter = e.submitter || null;
                window.confirmModal(optionsFrom(form)).then(function (yes) {
                    if (!yes) return;
                    form.__v1Confirmed = true;
                    if (form.requestSubmit) {
                        submitter ? form.requestSubmit(submitter) : form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            }, true);

            // <button|a data-confirm="..."> — ask on click, then repeat the click.
            document.addEventListener('click', function (e) {
                var el = e.target.closest && e.target.closest('[data-confirm]:not(form)');
                if (!el) return;
                if (el.__v1Confirmed) {
                    el.__v1Confirmed = false;
                    return;
                }
                e.preventDefault();
                e.stopImmediatePropagation();
                window.confirmModal(optionsFrom(el)).then(function (yes) {
                    if (!yes) return;
                    el.__v1Confirmed = true;
                    el.click();
                });
            }, true);
        })();
    </script>
@endonce
