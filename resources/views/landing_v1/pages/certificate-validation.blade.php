@extends('landing_v1.layouts.app')

@section('content')
<main>
    <header class="min-h-[40vh] flex items-center relative overflow-hidden" style="background: linear-gradient(135deg, #0F4C45 0%, #0a332e 100%);">
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
                    أدخل رقم الشهادة المطبوع (مثل <span class="font-mono font-bold text-[#E8D9C0]" dir="ltr">QEC-0000-028</span>) أو امسح رمز QR للتحقق الفوري.
                </p>
            </div>
        </div>
    </header>

    <section class="py-10 lg:py-16 bg-[#FAF8F4]">
        <div class="container">
            <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start">

                <div class="lg:col-span-3 bg-white rounded-20px border border-[#E0D4BC]/60 shadow-sm overflow-hidden">
                    <div class="px-6 lg:px-8 py-7">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="size-10 rounded-12px bg-primary/10 center shrink-0">
                                <span class="icon-[tabler--shield-check] size-6 text-primary"></span>
                            </div>
                            <div>
                                <h2 class="font-bold text-20px text-primary">تحقق من الشهادة</h2>
                                <p class="font-medium text-13px text-[#7A8886]">رقم QEC أو المعرف الرقمي + رمز التحقق</p>
                            </div>
                        </div>

                        @if(!empty($autoChecked) && !empty($prefilledId))
                            <div class="rounded-14px {{ !empty($prefilledResult['valid']) ? 'bg-[#F0FDF4] border-[#BBF7D0]' : 'bg-red-50 border-red-200' }} border px-4 py-3 flex items-center gap-3 mb-6">
                                <span class="size-8 rounded-full {{ !empty($prefilledResult['valid']) ? 'bg-[#0F4C45]' : 'bg-red-500' }} center shrink-0">
                                    <span class="icon-[tabler--qrcode] size-4 text-white"></span>
                                </span>
                                <p class="font-semibold text-13px {{ !empty($prefilledResult['valid']) ? 'text-[#065F46]' : 'text-red-700' }}">
                                    تم فتح رابط التحقق للمعرف
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
                                    value="{{ old('certificate_id', $prefilledId ?? '') }}"
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

                        <div id="certificate-result" class="mt-6">
                            @if(!is_null($prefilledResult))
                                @include('landing_v1.pages.certificate-validation-result', $prefilledResult)
                            @else
                                <div class="rounded-14px border border-dashed border-[#E0D4BC] bg-[#FAF8F4]/50 px-4 py-5 text-center">
                                    <p class="font-medium text-13px text-[#8E8F8F]">نتيجة التحقق ستظهر هنا بعد إدخال رقم الشهادة ورمز التحقق</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-20px border border-[#E0D4BC]/60 p-6">
                        <h3 class="font-bold text-18px text-primary mb-4">كيف أتحقق؟</h3>
                        <ol class="space-y-3">
                            <li class="flex gap-3">
                                <span class="size-7 rounded-full bg-primary text-white center font-bold text-12px shrink-0 mt-0.5">1</span>
                                <p class="font-medium text-14px text-[#3D455D] leading-relaxed">انسخ رقم الشهادة من الوثيقة (صيغة QEC)</p>
                            </li>
                            <li class="flex gap-3">
                                <span class="size-7 rounded-full bg-primary text-white center font-bold text-12px shrink-0 mt-0.5">2</span>
                                <p class="font-medium text-14px text-[#3D455D] leading-relaxed">أو امسح رمز QR لفتح صفحة التحقق تلقائياً</p>
                            </li>
                            <li class="flex gap-3">
                                <span class="size-7 rounded-full bg-primary text-white center font-bold text-12px shrink-0 mt-0.5">3</span>
                                <p class="font-medium text-14px text-[#3D455D] leading-relaxed">أدخل رمز التحقق واضغط <span class="font-bold text-primary">تحقق الآن</span></p>
                            </li>
                        </ol>
                    </div>

                    <div class="rounded-20px overflow-hidden border border-[#E0D4BC]/40 bg-white">
                        <div class="bg-gradient-to-br from-[#0F4C45] to-[#1a6b60] p-5">
                            <img src="{{ $sampleCertificateUrl }}" alt="نموذج شهادة QIEC" class="w-full rounded-10px border border-white/20 shadow-sm object-contain max-h-44 bg-white/5">
                        </div>
                        <div class="p-5">
                            <h4 class="font-bold text-16px text-primary mb-2">شهادات QIEC المعتمدة</h4>
                            <p class="font-medium text-13px text-[#7A8886] leading-relaxed">شهادة ثنائية اللغة برقم فريد ورمز QR للتحقق المباشر من هذه الصفحة.</p>
                        </div>
                    </div>

                    <div class="bg-primary rounded-20px p-6 text-white">
                        <h4 class="font-bold text-16px mb-2">لم تجد شهادتك؟</h4>
                        <p class="font-medium text-13px text-white/80 leading-relaxed mb-4">تأكد من الرقم كاملاً، أو تواصل مع الدعم إذا استمرت المشكلة.</p>
                        <a href="{{ route('landing.v1.contact') }}" class="inline-flex items-center gap-2 rounded-12px bg-white text-primary px-5 h-10 font-bold text-13px hover:bg-white/95 transition">
                            تواصل معنا <span class="icon-[tabler--arrow-left] size-4"></span>
                        </a>
                    </div>
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
                    resultBox.innerHTML = '<div class="rounded-14px bg-amber-50 border border-amber-200 p-4 text-center font-medium text-13px text-amber-800">انتهت الجلسة — حدّث الصفحة ثم أعد المحاولة</div>';
                }
                throw new Error('validation');
            }
            return data;
        })
        .then(data => {
            if (data && data.html) {
                resultBox.innerHTML = data.html;
                if (data.number) {
                    const idInput = form.querySelector('#certificate_id');
                    if (idInput) idInput.value = data.number;
                }
                resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        })
        .catch(err => {
            if (err.message !== 'validation') {
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
