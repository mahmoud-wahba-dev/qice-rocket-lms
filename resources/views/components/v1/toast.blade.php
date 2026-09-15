{{-- Shared floating toast host used by window.showCartToast (landing_v1.js) --}}
@once
    <div id="cart-toast"
        class="fixed bottom-6 start-6 z-[2147483646] flex items-center gap-3 bg-white border border-gray-100 shadow-xl rounded-10px px-5 py-4 transition-all duration-300 translate-y-20 opacity-0 pointer-events-none max-w-sm"
        style="z-index: 2147483646 !important;"
        role="status" aria-live="polite">
        <span id="cart-toast-icon" class="icon-[tabler--circle-check-filled] size-6 text-green-500 shrink-0"></span>
        <div class="min-w-0">
            <p id="cart-toast-title" class="font-bold text-14px text-primary"></p>
            <p id="cart-toast-msg" class="font-medium text-12px text-primary/60"></p>
        </div>
        <button type="button" onclick="typeof hideCartToast === 'function' && hideCartToast()"
            class="ms-auto text-primary/40 hover:text-primary transition shrink-0" aria-label="إغلاق">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    </div>
@endonce
