@php
    $v1FlashToast = session('toast');

    if (empty($v1FlashToast) && session()->has('success')) {
        $v1FlashToast = [
            'title' => 'تم',
            'msg' => session('success'),
            'type' => 'success',
        ];
    }

    if (empty($v1FlashToast) && session()->has('status')) {
        $v1FlashToast = [
            'title' => 'تم',
            'msg' => session('status'),
            'type' => 'success',
        ];
    }

    if (empty($v1FlashToast) && session()->has('msg')) {
        $v1FlashToast = [
            'title' => session('title') ?? 'تنبيه',
            'msg' => session('msg'),
            'type' => session('type') ?? 'success',
        ];
    }

    $v1FlashErrors = (isset($errors) && $errors->any()) ? $errors->all() : [];
@endphp

{{-- Always mount toast host so showCartToast works on every panel_v1 / course-player page --}}
@include('components.v1.toast')

@if (!empty($v1FlashToast))
    {{-- Inline (not only @push): Vite modules load deferred; retry until showCartToast exists --}}
    <script>
        (function () {
            var title = @json($v1FlashToast['title'] ?? 'تنبيه');
            var msg = @json($v1FlashToast['msg'] ?? '');
            var type = @json($v1FlashToast['type'] ?? ($v1FlashToast['status'] ?? 'success'));
            var tries = 0;

            function showFlashToast() {
                if (typeof window.showCartToast === 'function') {
                    window.showCartToast(title, msg, type);
                    return;
                }
                if (++tries < 40) {
                    setTimeout(showFlashToast, 50);
                    return;
                }
                var el = document.getElementById('v1-flash-toast-fallback');
                if (el) {
                    el.classList.remove('hidden');
                    setTimeout(function () { el.remove(); }, 5000);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showFlashToast);
            } else {
                showFlashToast();
            }
        })();
    </script>

    <div id="v1-flash-toast-fallback"
        class="hidden fixed bottom-6 start-6 z-[2147483646] max-w-sm rounded-12px px-5 py-4 shadow-lg border {{ in_array(($v1FlashToast['type'] ?? $v1FlashToast['status'] ?? ''), ['error', 'danger'], true) ? 'bg-[#FEF2F2] border-[#FECACA] text-[#B91C1C]' : 'bg-[#ECFDF5] border-[#A7F3D0] text-[#065F46]' }}"
        style="z-index: 2147483646 !important;"
        role="status" aria-live="polite">
        <div class="flex items-start gap-3">
            <p class="font-bold text-15px">{{ $v1FlashToast['title'] ?? '' }}</p>
            <button type="button" onclick="this.closest('#v1-flash-toast-fallback').remove()"
                class="ms-auto font-bold text-16px leading-none opacity-60 hover:opacity-100">×</button>
        </div>
        @if (!empty($v1FlashToast['msg']))
            <p class="font-medium text-14px mt-1">{{ $v1FlashToast['msg'] }}</p>
        @endif
    </div>
@elseif (!empty($v1FlashErrors))
    <script>
        (function () {
            var msg = @json($v1FlashErrors[0] ?? 'يرجى تصحيح الأخطاء');
            var tries = 0;
            function showErr() {
                if (typeof window.showCartToast === 'function') {
                    window.showCartToast('خطأ', msg, 'error');
                    return;
                }
                if (++tries < 40) setTimeout(showErr, 50);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showErr);
            } else {
                showErr();
            }
        })();
    </script>

    <div class="container mx-auto mt-6">
        <div class="rounded-12px bg-[#FEF2F2] border border-[#FECACA] px-5 py-4">
            <p class="font-bold text-15px text-[#B91C1C] mb-1">يرجى تصحيح الأخطاء التالية:</p>
            <ul class="list-disc ps-6 space-y-1">
                @foreach ($v1FlashErrors as $v1FlashError)
                    <li class="font-medium text-14px text-[#B91C1C]">{{ $v1FlashError }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
