@extends('landing_v1.layouts.app')

@section('content')
@php $landingImg = asset('assets/landing_v1/img/contact'); @endphp
<main>
    {{-- Hero --}}
    <header class="min-h-[44vh] flex items-center bg-primary relative overflow-hidden" style="background: linear-gradient(135deg, #0F4C45 0%, #0a332e 100%);">
        <div class="absolute inset-0 opacity-10" style="background-image: url('{{ $landingImg }}/hero.webp'); background-size: cover; background-position: center;"></div>
        <div class="container relative z-10 py-16">
            <div class="max-w-3xl">
                <nav class="breadcrumbs mb-4">
                    <ul class="flex items-center gap-2 text-white/80">
                        <li><a href="{{ route('landing.v1.index') }}" class="font-medium text-16px text-white/80 hover:text-white transition">الرئيسية</a></li>
                        <li class="icon-[tabler--chevron-left] size-4 text-white/60"></li>
                        <li><span class="font-medium text-16px text-white">التحقق من الشهادة</span></li>
                    </ul>
                </nav>
                <div class="flex items-start gap-4">
                    <div class="size-14 rounded-16px bg-white/10 border border-white/15 center shrink-0 hidden sm:flex">
                        <span class="icon-[tabler--certificate] size-7 text-[#E8D9C0]"></span>
                    </div>
                    <div>
                        <h1 class="font-bold text-36px lg:text-44px text-white leading-tight mb-3">التحقق من الشهادة</h1>
                        <p class="font-medium text-18px lg:text-20px text-white/80 leading-relaxed">أدخل رقم الشهادة للتحقق الفوري من صحتها وبيانات المتدرب والدورة — متوافق مع QR المطبوع على شهادات QIEC</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('panel.v1.student.certificates') }}" class="inline-flex items-center gap-2 rounded-12px bg-white text-primary px-6 h-11 font-bold text-14px hover:bg-white/95 transition">
                        <span class="icon-[tabler--award] size-5"></span> شهاداتي
                    </a>
                    <span class="inline-flex items-center gap-2 rounded-12px bg-white/10 border border-white/15 text-white px-5 h-11 font-semibold text-13px">معرّف رقمي + QR + ختم</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Validation Card --}}
    <section class="py-10 lg:py-16 bg-[#FAF8F4]">
        <div class="container">
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start">

                    {{-- Form --}}
                    <div class="lg:col-span-3 bg-white rounded-20px border border-[#E0D4BC]/60 shadow-sm overflow-hidden">
                        <div class="px-6 lg:px-8 py-7">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="size-10 rounded-12px bg-primary/10 center shrink-0">
                                    <span class="icon-[tabler--shield-check] size-6 text-primary"></span>
                                </div>
                                <div>
                                    <h2 class="font-bold text-20px lg:text-22px text-primary">تحقق من الشهادة</h2>
                                    <p class="font-medium text-13px text-[#7A8886]">أدخل المعرف المطبوع أو امسح QR</p>
                                </div>
                            </div>

                            @if(!empty($prefilledId))
                                <div class="rounded-14px bg-[#F0FDF4] border border-[#BBF7D0] px-4 py-3 flex items-center gap-3 mb-6">
                                    <span class="size-8 rounded-full bg-[#0F4C45] center shrink-0"><span class="icon-[tabler--qrcode] size-4 text-white"></span></span>
                                    <p class="font-semibold text-13px text-[#065F46]">رابط QR يحمل المعرف <span class="font-mono font-bold">#{{ $prefilledId }}</span> — تم التحميل تلقائياً</p>
                                </div>
                            @endif

                            <form id="certificate-validation-form" action="{{ url('/certificate_validation/validate') }}" method="POST" class="space-y-5">
                                @csrf
                                <div>
                                    <label for="certificate_id" class="label-text font-semibold text-14px text-primary mb-2 flex items-center gap-2">
                                        <span class="icon-[tabler--hash] size-4 text-primary/50"></span> رقم الشهادة <span class="text-[#E11D48]">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" inputmode="numeric" name="certificate_id" id="certificate_id"
                                            value="{{ old('certificate_id', $prefilledId ?? '') }}"
                                            placeholder="مثال: 12345"
                                            class="input w-full h-14 rounded-12px bg-[#F7F7F7] border border-[#E8E8E8] pr-11 text-16px font-semibold text-primary placeholder:text-[#8E8F8F] focus:border-primary focus:bg-white transition">
                                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                            <span class="size-7 rounded-8px bg-white border border-[#E8E8E8] center"><span class="icon-[tabler--certificate] size-4 text-primary/60"></span></span>
                                        </span>
                                    </div>
                                    <p class="invalid-feedback text-12px text-[#E11D48] mt-2 hidden" data-error="certificate_id"></p>
                                    <p class="font-medium text-11px text-[#8E8F8F] mt-2">تجد الرقم أسفل الشهادة بجانب QR — مثال: <span class="font-mono font-bold text-primary">#1024</span></p>
                                </div>

                                <div>
                                    <label class="label-text font-semibold text-14px text-primary mb-2">رمز التحقق <span class="text-[#E11D48]">*</span></label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="relative">
                                            <input type="text" name="captcha" id="captcha_input" placeholder="أدخل الرمز الظاهر"
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

                                <button type="submit" id="validateBtn"
                                    class="btn btn-primary w-full h-14 rounded-12px font-bold text-16px gap-2">
                                    <span class="icon-[tabler--shield-check] size-5"></span>
                                    تحقق الآن
                                    <span id="validateSpinner" class="loading loading-spinner size-4 hidden"></span>
                                </button>

                                <p class="font-medium text-12px text-[#7A8886] text-center flex items-center justify-center gap-2">
                                    <span class="icon-[tabler--lock] size-4"></span> التحقق آمن ويتم عبر قاعدة بيانات QIEC مباشرة
                                </p>
                            </form>

                            {{-- Result container for AJAX --}}
                            <div id="certificate-result" class="mt-6">
                                @if(!empty($prefilledResult))
                                    @include('landing_v1.pages.certificate-validation-result', $prefilledResult)
                                @else
                                    <div class="rounded-14px border border-dashed border-[#E0D4BC] bg-[#FAF8F4]/50 px-4 py-5 text-center">
                                        <p class="font-medium text-13px text-[#8E8F8F]">نتيجة التحقق ستظهر هنا بعد إدخال رقم الشهادة ورمز التحقق</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Info Side --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-20px border border-[#E0D4BC]/60 p-6 lg:p-7">
                            <h3 class="font-bold text-18px text-primary mb-3">كيف أتحقق؟</h3>
                            <ol class="space-y-3">
                                <li class="flex gap-3">
                                    <span class="size-7 rounded-full bg-primary text-white center font-bold text-12px shrink-0 mt-0.5">1</span>
                                    <p class="font-medium text-14px text-[#3D455D] leading-relaxed">انسخ رقم الشهادة من أسفل الوثيقة (بجانب QR)</p>
                                </li>
                                <li class="flex gap-3">
                                    <span class="size-7 rounded-full bg-primary text-white center font-bold text-12px shrink-0 mt-0.5">2</span>
                                    <p class="font-medium text-14px text-[#3D455D] leading-relaxed">امسح QR بكاميرا الجوال — يفتح نفس الصفحة تلقائياً</p>
                                </li>
                                <li class="flex gap-3">
                                    <span class="size-7 rounded-full bg-primary text-white center font-bold text-12px shrink-0 mt-0.5">3</span>
                                    <p class="font-medium text-14px text-[#3D455D] leading-relaxed">أدخل رمز التحقق واضغط <span class="font-bold text-primary">تحقق الآن</span></p>
                                </li>
                            </ol>
                            <div class="mt-5 rounded-14px bg-[#FAF8F4] border border-[#E0D4BC]/40 p-4 flex gap-3">
                                <span class="icon-[tabler--info-circle] size-5 text-primary shrink-0 mt-0.5"></span>
                                <p class="font-medium text-12px text-[#7A8886] leading-relaxed">الشهادة صالحة فقط إذا ظهرت بيانات المتدرب والدورة مطابقة لما في الوثيقة. كل شهادة تحمل QR فريد مرتبط بـ <span class="font-mono font-bold text-primary">certificate_id</span></p>
                            </div>
                        </div>

                        <div class="rounded-20px overflow-hidden border border-[#E0D4BC]/40 bg-white">
                            @php
                                $mainImage = getThemePageBackgroundSettings('certificate_validation');
                                $overlayImage = getThemePageBackgroundSettings('certificate_validation_overlay_image');
                            @endphp
                            @if(!empty($mainImage))
                                <img src="{{ $mainImage }}" alt="تحقق الشهادة" class="w-full h-48 object-cover">
                            @else
                                <div class="h-48 bg-gradient-to-br from-[#0F4C45] to-[#1a6b60] flex items-center justify-center p-6">
                                    <div class="text-center">
                                        <span class="icon-[tabler--certificate] size-12 text-white/90 mx-auto mb-3"></span>
                                        <p class="font-bold text-18px text-white">شهادات QIEC معتمدة</p>
                                        <p class="font-medium text-13px text-white/70 mt-1">تحمل QR وختم رقمي</p>
                                    </div>
                                </div>
                            @endif
                            @if(!empty($overlayImage))
                                <div class="p-4 bg-[#FAF8F4] border-t border-[#E0D4BC]/40">
                                    <img src="{{ $overlayImage }}" alt="overlay" class="w-full h-auto rounded-12px">
                                </div>
                            @endif
                            <div class="p-6">
                                <h4 class="font-bold text-16px text-primary mb-2">شهادات مهنية معتمدة</h4>
                                <p class="font-medium text-13px text-[#7A8886] leading-relaxed">صادرة بعد إتمام 100% من متطلبات الدورة أو النجاح في الاختبار المعتمد، مطبوعة بخط Vazir و QR للتحقق الفوري.</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 text-primary px-3 py-1.5 font-bold text-11px"><span class="icon-[tabler--check] size-3.5"></span> إتمام دورة</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EDE9FE] text-[#6D28D9] px-3 py-1.5 font-bold text-11px"><span class="icon-[tabler--list-check] size-3.5"></span> اختبار</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FEF3C7] text-[#92400E] px-3 py-1.5 font-bold text-11px"><span class="icon-[tabler--package] size-3.5"></span> حزمة</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-primary rounded-20px p-6 text-white">
                            <h4 class="font-bold text-16px mb-2 flex items-center gap-2"><span class="icon-[tabler--help-circle] size-5 text-[#E8D9C0]"></span> لم تجد شهادتك؟</h4>
                            <p class="font-medium text-13px text-white/80 leading-relaxed mb-4">تأكد من الرقم أو تواصل مع الدعم. الشهادة تُصدر فقط بعد إكمال التقدم 100% أو نجاح الاختبار.</p>
                            <a href="{{ route('landing.v1.contact') }}" class="inline-flex items-center gap-2 rounded-12px bg-white text-primary px-5 h-10 font-bold text-13px hover:bg-white/95 transition">
                                تواصل معنا <span class="icon-[tabler--arrow-left] size-4"></span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const captchaImg = document.getElementById('captchaImage');
    const refreshBtn = document.getElementById('refreshCaptchaLanding');
    const form = document.getElementById('certificate-validation-form');
    const resultBox = document.getElementById('certificate-result');
    const btn = document.getElementById('validateBtn');
    const spinner = document.getElementById('validateSpinner');

    function refreshCaptcha() {
        fetch('{{ url('/captcha/create') }}', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}})
            .then(r=>r.json()).then(d=>{ if(d.captcha_src) captchaImg.src = d.captcha_src; })
            .catch(()=>{ captchaImg.src = '{{ captcha_src('flat') }}?'+Date.now(); });
    }
    refreshCaptcha();
    if(refreshBtn) refreshBtn.addEventListener('click', refreshCaptcha);

    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            btn.disabled = true; spinner.classList.remove('hidden');
            form.querySelectorAll('.invalid-feedback').forEach(el=>{el.classList.add('hidden'); el.textContent='';});
            form.querySelectorAll('.is-invalid').forEach(el=>el.classList.remove('is-invalid'));

            fetch(form.action, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: formData
            })
            .then(async r=>{
                const data = await r.json().catch(()=>null);
                if(!r.ok) {
                    if(data && data.errors) {
                        Object.keys(data.errors).forEach(k=>{
                            const msg = data.errors[k][0];
                            const errEl = form.querySelector('[data-error="'+k+'"]');
                            const input = form.querySelector('[name="'+k+'"]');
                            if(errEl){ errEl.textContent = msg; errEl.classList.remove('hidden'); }
                            if(input){ input.classList.add('is-invalid','border-[#E11D48]'); }
                        });
                    }
                    throw new Error('validation');
                }
                return data;
            })
            .then(data=>{
                if(data && data.html) {
                    resultBox.innerHTML = data.html;
                    resultBox.scrollIntoView({behavior:'smooth', block:'start'});
                }
            })
            .catch(err=>{
                if(err.message !== 'validation') {
                    resultBox.innerHTML = '<div class="rounded-14px bg-red-50 border border-red-200 p-4 text-center font-medium text-13px text-red-600">حدث خطأ، حاول مرة أخرى</div>';
                }
            })
            .finally(()=>{
                refreshCaptcha();
                btn.disabled = false; spinner.classList.add('hidden');
            });
        });
    }
});
</script>
@endpush
