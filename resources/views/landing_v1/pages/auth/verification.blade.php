@extends('landing_v1.layouts.app')

@section('content')
    @php
        $landingImg = asset('assets/landing_v1/img');
        $isEmail = ($username ?? 'email') === 'email';
        $maskedContact = $usernameValue ?? '';
        if ($isEmail && str_contains($maskedContact, '@')) {
            [$local, $domain] = explode('@', $maskedContact, 2);
            $maskedContact = (strlen($local) > 2 ? substr($local, 0, 2) . '***' : '***') . '@' . $domain;
        } elseif (!$isEmail && strlen($maskedContact) > 4) {
            $maskedContact = '***' . substr($maskedContact, -4);
        }
        $resendDurationMinutes = $resendDurationMinutes ?? 2;
    @endphp

    <main class="bg-white overflow-hidden">
        <header class="m-16 rounded-20px bg-secondary px-9 py-15 shadow">
            <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-20 gap-4">
                <div>
                    <p class="font-semibold text-18px text-primary mb-2">{{ trans('update.thank_you_for_joining') }} 😊</p>
                    <h1 class="font-semibold text-36px text-primary mb-4">{{ trans('update.account_verification') }}</h1>

                    @if (!empty($authIntentMessage))
                        <p class="font-medium text-16px text-primary/80 mb-4 rounded-8px bg-primary/5 border border-primary/15 px-4 py-3">
                            {{ $authIntentMessage }}
                        </p>
                    @endif

                    <div class="rounded-10px border border-primary/15 bg-primary/5 px-5 py-4 mb-8">
                        <p class="font-medium text-15px text-primary/80 leading-relaxed">
                            @if ($isEmail)
                                {{ trans('update.account_verification_code_hint_for_email') }}
                            @else
                                {{ trans('update.account_verification_code_hint_for_mobile') }}
                            @endif
                        </p>
                        @if (!empty($maskedContact))
                            <p class="font-bold text-14px text-primary mt-2" dir="ltr">{{ $maskedContact }}</p>
                        @endif
                    </div>

                    @if (!empty($mailNotConfigured) && !empty($isLocal))
                        <p class="font-medium text-13px text-amber-700 bg-amber-50 border border-amber-200 rounded-8px px-4 py-3 mb-6">
                            البريد غير مُعدّ على السيرفر المحلي. راجع <code class="text-12px">storage/logs/laravel.log</code> للحصول على رمز التحقق أثناء التطوير.
                        </p>
                    @endif

                    <form method="POST" action="/verification" id="verification-form" class="verification-form">
                        @csrf
                        <input type="hidden" name="username" value="{{ $username }}">
                        <input type="hidden" name="usernameValue" value="{{ $usernameValue }}">

                        <label class="label label-text font-medium text-18px text-primary mb-4 block">
                            {{ trans('update.verification_code') }}
                        </label>

                        <div class="flex gap-3 justify-center direction-ltr mb-6" dir="ltr">
                            @foreach ([1, 2, 3, 4, 5] as $num)
                                <input type="tel" name="code[{{ $num }}]" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                    class="verification-code-input size-14 sm:size-16 text-center text-24px font-bold rounded-10px border-2 border-gray-200 bg-f7 text-primary focus:border-primary focus:outline-none"
                                    autocomplete="one-time-code" aria-label="رقم {{ $num }}">
                            @endforeach
                        </div>

                        @error('code')
                            <p class="text-14px text-red-500 mb-4 text-center">{{ $message }}</p>
                        @enderror

                        <button type="submit" id="verify-submit-btn"
                            class="btn btn-primary btn-block h-16 rounded-8px font-semibold text-20px w-full">
                            {{ trans('update.verify') }}
                        </button>
                    </form>

                    <div class="mt-8 text-center">
                        <p id="resend-wait" class="font-medium text-14px text-primary/50 mb-2 hidden">
                            {{ trans('update.please_wait_2') }}
                            <span id="resend-timer" class="font-bold text-primary tabular-nums"></span>
                            {{ trans('update.to_resend_the_code') }}
                        </p>
                        <p id="resend-prompt" class="font-medium text-14px text-primary/60 mb-2 hidden">
                            {{ trans('update.haven’t_received_the_code') }}
                        </p>
                        <button type="button" id="resend-code-btn"
                            class="font-bold text-16px text-primary hover:text-primary/80 disabled:opacity-40 disabled:cursor-not-allowed"
                            disabled>
                            {{ trans('auth.resend_code') }}
                        </button>
                    </div>
                </div>

                <x-landing_v1::auth-slider />
            </div>
        </header>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('verification-form');
            const inputs = Array.from(document.querySelectorAll('.verification-code-input'));
            const resendBtn = document.getElementById('resend-code-btn');
            const resendWait = document.getElementById('resend-wait');
            const resendPrompt = document.getElementById('resend-prompt');
            const resendTimerEl = document.getElementById('resend-timer');
            const submitBtn = document.getElementById('verify-submit-btn');
            const resendMinutes = {{ (int) $resendDurationMinutes }};
            let canResend = false;
            let timerInterval = null;

            function startResendTimer() {
                canResend = false;
                resendBtn.disabled = true;
                resendWait.classList.remove('hidden');
                resendPrompt.classList.add('hidden');

                let secondsLeft = resendMinutes * 60;

                const tick = () => {
                    const m = Math.floor(secondsLeft / 60);
                    const s = secondsLeft % 60;
                    resendTimerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

                    if (secondsLeft <= 0) {
                        clearInterval(timerInterval);
                        canResend = true;
                        resendBtn.disabled = false;
                        resendWait.classList.add('hidden');
                        resendPrompt.classList.remove('hidden');
                    }
                    secondsLeft--;
                };

                tick();
                timerInterval = setInterval(tick, 1000);
            }

            inputs.forEach((input, index) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '').slice(0, 1);
                    if (input.value && inputs[index + 1]) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && inputs[index - 1]) {
                        inputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 5);
                    pasted.split('').forEach((char, i) => {
                        if (inputs[i]) inputs[i].value = char;
                    });
                    if (inputs[pasted.length - 1]) inputs[pasted.length - 1].focus();
                });
            });

            if (inputs[0]) inputs[0].focus();

            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.textContent = 'جاري التحقق...';
            });

            resendBtn.addEventListener('click', async () => {
                if (!canResend) return;

                resendBtn.disabled = true;
                resendBtn.textContent = 'جاري الإرسال...';

                try {
                    const response = await fetch('/verification/resend', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await response.json();

                    if (data.code === 200) {
                        inputs.forEach(i => { i.value = ''; });
                        if (inputs[0]) inputs[0].focus();
                        if (typeof showCartToast === 'function') {
                            showCartToast(data.title || 'تم', data.msg || 'تم إرسال الرمز مجدداً', 'success');
                        }
                        startResendTimer();
                    }
                } catch (_) {
                    if (typeof showCartToast === 'function') {
                        showCartToast('خطأ', 'تعذّر إعادة إرسال الرمز', 'error');
                    }
                } finally {
                    resendBtn.textContent = '{{ trans('auth.resend_code') }}';
                    if (canResend) resendBtn.disabled = false;
                }
            });

            startResendTimer();
        });
    </script>
@endpush
