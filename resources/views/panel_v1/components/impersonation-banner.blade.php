@if (!empty($isImpersonating))
    <div class="impersonation-banner w-full shrink-0 bg-primary text-white border-b border-white/20"
        role="status" aria-live="polite"
        style="background-color:#0F3D36;color:#fff;">
        <div class="px-3 sm:px-5 py-2.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
            <p class="font-semibold text-12px sm:text-13px leading-snug min-w-0" style="color:#fff;">
                وضع اختبار المشرف — أنت مسجّل الدخول كـ{{ $impersonatedRoleLabel ?? 'مستخدم' }}:
                <span class="font-bold">{{ $impersonatedName ?? 'مستخدم في النظام' }}</span>
            </p>
            <a href="{{ $leaveImpersonationUrl ?? route('panel.v1.leave-impersonation') }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-8px px-3 h-9 font-bold text-12px transition shrink-0"
                style="background-color:rgba(255,255,255,0.18);color:#fff;">
                <span class="icon-[tabler--arrow-narrow-left] size-4 shrink-0" aria-hidden="true"></span>
                العودة إلى لوحة المشرف
            </a>
        </div>
    </div>
@endif

