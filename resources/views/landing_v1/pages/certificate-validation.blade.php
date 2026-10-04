@extends('landing_v1.layouts.app')

@section('content')
@php
    $hasAutoResult = !is_null($prefilledResult ?? null);
    $isValidAuto = !empty($prefilledResult['valid']);
@endphp
<main>
    <header class="min-h-[36vh] flex items-center relative overflow-hidden" style="background: linear-gradient(135deg, #0F4C45 0%, #0a332e 100%);">
        <div class="container relative z-10 py-14">
            <div class="max-w-3xl">
                <nav class="breadcrumbs mb-4">
                    <ul class="flex items-center gap-2 text-white/80">
                        <li><a href="{{ route('landing.v1.index') }}" class="font-medium text-16px text-white/80 hover:text-white transition">الرئيسية</a></li>
                        <li class="icon-[tabler--chevron-left] size-4 text-white/60"></li>
                        <li><span class="font-medium text-16px text-white">التحقق من الشهادة</span></li>
                    </ul>
                </nav>
                <h1 class="font-bold text-36px lg:text-44px text-white leading-tight mb-3">التحقق من الشهادة</h1>
                <p class="font-medium text-18px text-white/80 leading-relaxed max-w-2xl">
                    صفحة عامة للجهات الخارجية (جهات العمل / المدققين) للتأكد من صحة الشهادة عبر رقم QEC أو مسح QR.
                </p>
            </div>
        </div>
    </header>

    <section class="py-10 lg:py-16 bg-[#FAF8F4]">
        <div class="container">
            <div class="max-w-3xl mx-auto space-y-6">

                {{-- Result first when opened via QR / deep link --}}
                <div id="certificate-result" class="{{ $hasAutoResult ? '' : 'hidden' }}">
                    @if($hasAutoResult)
                        @include('landing_v1.pages.certificate-validation-result', $prefilledResult)
                    @else
                        <div class="rounded-14px border border-dashed border-[#E0D4BC] bg-[#FAF8F4]/50 px-4 py-5 text-center">
                            <p class="font-medium text-13px text-[#8E8F8F]">أدخل رقم الشهادة ورمز التحقق لعرض النتيجة</p>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-20px border border-[#E0D4BC]/60 shadow-sm overflow-hidden">
                    <div class="px-6 lg:px-8 py-7">
                        @if($hasAutoResult && $isValidAuto)
                            <button type="button" id="toggle-manual-check"
                                class="w-full flex items-center justify-between gap-3 rounded-14px border border-[#E0D4BC]/60 bg-[#FAF8F4] px-4 py-3.5 text-start hover:bg-[#F3EFE6] transition">
                                <span class="flex items-center gap-3">
                                    <span class="size-10 rounded-12px bg-primary/10 center shrink-0">
                                        <span class="icon-[tabler--search] size-5 text-primary"></span>
                                    </span>
                                    <span>
                                        <span class="block font-bold text-15px text-primary">التحقق من شهادة أخرى</span>
                                        <span class="block font-medium text-12px text-[#7A8886]">للزوار الذين يدخلون الرقم يدوياً</span>
                                    </span>
                                </span>
                                <span class="icon-[tabler--chevron-down] size-5 text-primary transition" data-chevron></span>
                            </button>
                            <div id="manual-check-panel" class="hidden mt-6">
                        @else
                            <div class="flex items-center gap-3 mb-6">
                                <div class="size-10 rounded-12px bg-primary/10 center shrink-0">
                                    <span class="icon-[tabler--shield-check] size-6 text-primary"></span>
                                </div>
                                <div>
                                    <h2 class="font-bold text-20px text-primary">تحقق من الشهادة</h2>
                                    <p class="font-medium text-13px text-[#7A8886]">أدخل رقم QEC الظاهر على الوثيقة</p>
                                </div>
                            </div>
                            <div id="manual-check-panel">
                        @endif

                            @if(!empty($autoChecked) && !empty($prefilledId) && empty($isValidAuto))
                                <div class="rounded-14px bg-red-50 border border-red-200 px-4 py-3 flex items-center gap-3 mb-6">
                                    <span class="size-8 rounded-full bg-red-500 center shrink-0">
                                        <span class="icon-[tabler--qrcode] size-4 text-white"></span>
                                    </span>
                                    <p class="font-semibold text-13px text-red-700">
                                        لم يُعثر على شهادة للمعرف
                                        <span class="font-mono font-bold" dir="ltr">{{ $prefilledId }}</span>
                                    </p>
                                </div>
                            @endif

                            <form id="certificate-validation-form" action="{{ url('/certificate_validation/validate') }}" method="POST" class="space-y-5">
                                @csrf
                                <div>
                                    <label for="certificate_id" class="label-text font-semibold text-14px text-primary mb-2 block">
                                        رقم الشهادة <span class="text-[#E11D48]">*</span>
                                    </label>
                                    <input type="text" name="certificate_id" id="certificate_id"
                                        value="{{ old('certificate_id', (!$hasAutoResult || !$isValidAuto) ? ($prefilledId ?? '') : '') }}"
                                        placeholder="مثال: QEC-0000-028 أو 28"
                                        autocomplete="off"
                                        class="input w-full h-14 rounded-12px bg-[#F7F7F7] border border-[#E8E8E8] text-16px font-semibold text-primary placeholder:text-[#8E8F8F] focus:border-primary focus:bg-white transition"
                                        dir="ltr">
                                    <p class="invalid-feedback text-12px text-[#E11D48] mt-2 hidden" data-error="certificate_id"></p>
                                </div>

                                <div>
                                    <label class="label-text font-semibold text-14px text-primary mb-2 block">رمز التحقق <span class="text-[#E11D48]">*</span></label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" name="captcha" id="captcha_input" placeholder="أدخل الرمز الظاهر"
                                                autocomplete="off"
                                                class="input w-full h-14 rounded-12px bg-[#F7F7F7] border border-[#E8E8E8] text-16px font-semibold text-primary placeholder:text-[#8E8F8F] focus:border-primary focus:bg-white transition">
                                            <p class="invalid-feedback text-12px text-[#E11D48] mt-2 hidden" data-error="captcha"></p>
                                        </div>
                                        <div class="flex items-center gap-2 bg-white rounded-12px border border-[#E8E8E8] p-2 h-14">
                                            <div class="flex-1 flex items-center justify-center bg-[#F7F7F7] rounded-10px h-full overflow-hidden">
                                                <img id="captchaImage" class="h-10 w-auto object-contain" src="{{ captcha_src('flat') }}" alt="captcha">
                                            </div>
                                            <button type="button" id="refreshCaptchaLanding" class="size-10 rounded-10px bg-primary/10 hover:bg-primary/15 center shrink-0 transition" aria-label="تحديث الرمز">
                                                <span class="icon-[tabler--refresh] size-5 text-primary"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" id="validateBtn" class="btn btn-primary w-full h-14 rounded-12px font-bold text-16px gap-2">
                                    <span class="icon-[tabler--shield-check] size-5"></span>
                                    تحقق الآن
                                    <span id="validateSpinner" class="loading loading-spinner size-4 hidden"></span>
                                </button>
                            </form>

                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-20px border border-[#E0D4BC]/60 p-6">
                    <h3 class="font-bold text-16px text-primary mb-2">لمن هذه الصفحة؟</h3>
                    <p class="font-medium text-13px text-[#7A8886] leading-relaxed">
                        ليست لوحة إدارة. هي رابط عام يُطبع مع QR على الشهادة حتى يتمكن أي طرف خارجي من التأكد أن الشهادة صادرة فعلاً من المركز.
                        إدارة الشهادات تتم من لوحة التحكم: إنشاء / تحميل / قائمة الشهادات.
                    </p>
                </div>

            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const captchaImg = document.getElementById('captchaImage');
    const refreshBtn = document.getElementById('refreshCaptchaLanding');
    const form = document.getElementById('certificate-validation-form');
    const resultBox = document.getElementById('certificate-result');
    const btn = document.getElementById('validateBtn');
    const spinner = document.getElementById('validateSpinner');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const toggleBtn = document.getElementById('toggle-manual-check');
    const manualPanel = document.getElementById('manual-check-panel');

    if (toggleBtn && manualPanel) {
        toggleBtn.addEventListener('click', function () {
            const open = manualPanel.classList.toggle('hidden') === false;
            const chevron = toggleBtn.querySelector('[data-chevron]');
            if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
        });
    }

    function refreshCaptcha() {
        if (!captchaImg) return;
        fetch('{{ url('/captcha/create') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(d => { if (d.captcha_src) captchaImg.src = d.captcha_src; })
        .catch(() => { captchaImg.src = '{{ url('/captcha/flat') }}?' + Date.now(); });
    }

    refreshCaptcha();
    if (refreshBtn) refreshBtn.addEventListener('click', refreshCaptcha);

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        btn.disabled = true;
        spinner.classList.remove('hidden');

        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid', 'border-[#E11D48]');
        });

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(async (r) => {
            const data = await r.json().catch(() => null);
            if (!r.ok) {
                if (data && data.errors) {
                    Object.keys(data.errors).forEach(k => {
                        const msg = data.errors[k][0];
                        const errEl = form.querySelector('[data-error="' + k + '"]');
                        const input = form.querySelector('[name="' + k + '"]');
                        if (errEl) {
                            errEl.textContent = msg;
                            errEl.classList.remove('hidden');
                        }
                        if (input) input.classList.add('is-invalid', 'border-[#E11D48]');
                    });
                } else if (r.status === 419) {
                    if (resultBox) {
                        resultBox.innerHTML = '<div class="rounded-14px bg-amber-50 border border-amber-200 p-4 text-center font-medium text-13px text-amber-800">انتهت الجلسة — حدّث الصفحة ثم أعد المحاولة</div>';
                    }
                }
                throw new Error('validation');
            }
            return data;
        })
        .then(data => {
            if (data && data.html && resultBox) {
                resultBox.innerHTML = data.html;
                resultBox.classList.remove('hidden');
                if (data.number) {
                    const idInput = form.querySelector('#certificate_id');
                    if (idInput) idInput.value = data.number;
                }
                resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        })
        .catch(err => {
            if (err.message !== 'validation' && resultBox) {
                resultBox.innerHTML = '<div class="rounded-14px bg-red-50 border border-red-200 p-4 text-center font-medium text-13px text-red-600">حدث خطأ، حاول مرة أخرى</div>';
            }
        })
        .finally(() => {
            refreshCaptcha();
            const captchaInput = form.querySelector('#captcha_input');
            if (captchaInput) captchaInput.value = '';
            btn.disabled = false;
            spinner.classList.add('hidden');
        });
    });
});
</script>
@endpush
