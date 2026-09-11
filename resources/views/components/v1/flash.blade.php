@php
    $v1FlashToast = session('toast');
    $v1FlashErrors = $errors->any() ? $errors->all() : [];
@endphp

@if (!empty($v1FlashToast))
    <div id="v1-flash-toast"
        class="fixed top-6 start-6 z-[999] max-w-sm rounded-12px px-5 py-4 shadow-lg border {{ ($v1FlashToast['type'] ?? '') === 'error' ? 'bg-[#FEF2F2] border-[#FECACA] text-[#B91C1C]' : 'bg-[#ECFDF5] border-[#A7F3D0] text-[#065F46]' }}">
        <div class="flex items-start gap-3">
            <p class="font-bold text-15px">{{ $v1FlashToast['title'] ?? '' }}</p>
            <button type="button" onclick="this.closest('#v1-flash-toast').remove()"
                class="ms-auto font-bold text-16px leading-none opacity-60 hover:opacity-100">×</button>
        </div>
        @if (!empty($v1FlashToast['msg']))
            <p class="font-medium text-14px mt-1">{{ $v1FlashToast['msg'] }}</p>
        @endif
    </div>
    <script>
        setTimeout(function () {
            var el = document.getElementById('v1-flash-toast');
            if (el) el.remove();
        }, 5000);
    </script>
@endif

@if (!empty($v1FlashErrors))
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
