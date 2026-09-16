@php
    $agoraEnabled = $agoraEnabled ?? !empty(getFeaturesSettings('agora_for_meeting'));
@endphp

{{-- Shared create/update meeting link modal (Rocket create-session equivalent) --}}
<dialog id="consultation-session-modal" class="modal">
    <div class="modal-box max-w-lg rounded-16px p-0 overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-d9">
            <h3 class="font-bold text-18px text-primary" id="consultation-session-title">إعداد رابط اللقاء</h3>
            <form method="dialog">
                <button type="submit" class="size-9 rounded-full bg-fa center hover:bg-[#E8ECEA] transition" aria-label="إغلاق">
                    <span class="icon-[tabler--x] size-5 text-gray"></span>
                </button>
            </form>
        </div>

        <form id="consultation-session-form" method="POST" action="#" class="px-5 py-5 space-y-4">
            @csrf
            <p class="font-medium text-13px text-gray" id="consultation-session-student"></p>

            @if ($agoraEnabled)
                <div>
                    <p class="font-semibold text-14px text-primary mb-2">نوع الجلسة</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 border border-d9 rounded-12px px-3 py-3 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="session_type" value="agora" class="radio radio-primary" checked>
                            <span class="font-medium text-14px text-primary">جلسة مباشرة (Agora)</span>
                        </label>
                        <label class="flex items-center gap-2 border border-d9 rounded-12px px-3 py-3 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="session_type" value="external" class="radio radio-primary">
                            <span class="font-medium text-14px text-primary">رابط خارجي</span>
                        </label>
                    </div>
                </div>
            @else
                <input type="hidden" name="session_type" value="external">
            @endif

            <div id="consultation-external-fields" class="{{ $agoraEnabled ? 'hidden' : '' }} space-y-3">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">رابط اللقاء <span class="text-red-500">*</span></label>
                    <input type="url" name="url" id="consultation-session-url"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px"
                        placeholder="https://zoom.us/j/... أو https://meet.google.com/...">
                    <p class="mt-1.5 font-medium text-12px text-gray">ضع رابط Zoom / Google Meet / Teams الحقيقي — لا تستخدم example.com</p>
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">كلمة المرور (اختياري)</label>
                    <input type="text" name="password" id="consultation-session-password"
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px"
                        placeholder="إن وُجدت">
                </div>
            </div>

            @if ($agoraEnabled)
                <div id="consultation-agora-hint" class="rounded-12px bg-[#ECFDF5] border border-[#A7F3D0] px-4 py-3">
                    <p class="font-medium text-13px text-[#065F46]">ستُفتح جلسة مباشرة داخل المنصة عبر Agora (نفس نظام Rocket).</p>
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2 pt-2">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-12px bg-primary text-white font-semibold text-15px hover:opacity-90 transition">
                    حفظ وبدء اللقاء
                </button>
                <button type="button" data-consultation-session-close
                    class="inline-flex items-center justify-center h-11 px-4 rounded-12px border border-d9 font-semibold text-15px text-gray hover:bg-fa transition">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
(() => {
    const modal = document.getElementById('consultation-session-modal');
    const form = document.getElementById('consultation-session-form');
    if (!modal || !form) return;

    const studentEl = document.getElementById('consultation-session-student');
    const urlEl = document.getElementById('consultation-session-url');
    const passwordEl = document.getElementById('consultation-session-password');
    const externalFields = document.getElementById('consultation-external-fields');
    const agoraHint = document.getElementById('consultation-agora-hint');

    const syncType = () => {
        const type = form.querySelector('input[name="session_type"]:checked')?.value
            || form.querySelector('input[name="session_type"]')?.value
            || 'external';
        const isExternal = type === 'external';
        if (externalFields) externalFields.classList.toggle('hidden', !isExternal);
        if (agoraHint) agoraHint.classList.toggle('hidden', isExternal);
        if (urlEl) urlEl.required = isExternal;
    };

    form.querySelectorAll('input[name="session_type"]').forEach((el) => {
        el.addEventListener('change', syncType);
    });

    document.querySelectorAll('[data-consultation-session-open]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const action = btn.getAttribute('data-action');
            const name = btn.getAttribute('data-student') || '';
            const link = btn.getAttribute('data-link') || '';
            form.action = action || '#';
            if (studentEl) studentEl.textContent = name ? ('الطالب: ' + name) : '';
            if (urlEl) urlEl.value = link;
            if (passwordEl) passwordEl.value = '';
            const externalRadio = form.querySelector('input[name="session_type"][value="external"]');
            const agoraRadio = form.querySelector('input[name="session_type"][value="agora"]');
            if (link && externalRadio) {
                externalRadio.checked = true;
            } else if (agoraRadio) {
                agoraRadio.checked = true;
            }
            syncType();
            if (typeof modal.showModal === 'function') modal.showModal();
        });
    });

    document.querySelectorAll('[data-consultation-session-close]').forEach((btn) => {
        btn.addEventListener('click', () => modal.close());
    });

    syncType();
})();
</script>
