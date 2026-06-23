{{-- Landing V1 — Cart Drawer Empty State --}}
<div class="flex flex-col items-center justify-center h-full min-h-[280px] gap-4 px-6 text-center">
    <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center">
        <svg width="36" height="42" viewBox="0 0 25 29" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-primary/25">
            <path d="M4.33074 8.58362H20.3943C20.8028 8.58357 21.2065 8.67186 21.5777 8.84244C21.949 9.01302 22.2789 9.26186 22.5449 9.57189C22.8109 9.88192 23.0068 10.2458 23.119 10.6386C23.2312 11.0314 23.2572 11.4438 23.1951 11.8476L21.4172 23.3963C21.2628 24.3999 20.7542 25.3151 19.9835 25.9762C19.2127 26.6373 18.2308 27.0006 17.2153 27.0003H7.50833C6.49314 27.0002 5.51151 26.6368 4.74105 25.9757C3.9706 25.3147 3.46222 24.3997 3.30791 23.3963L1.52999 11.8476C1.4679 11.4438 1.49385 11.0314 1.60607 10.6386C1.7183 10.2458 1.91414 9.88192 2.18016 9.57189C2.44619 9.26186 2.77612 9.01302 3.14732 8.84244C3.51852 8.67186 3.92222 8.58357 4.33074 8.58362Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8.11328 12.8303V5.74695C8.11328 4.61978 8.56105 3.53877 9.35808 2.74174C10.1551 1.94471 11.2361 1.49695 12.3633 1.49695C13.4905 1.49695 14.5715 1.94471 15.3685 2.74174C16.1655 3.53877 16.6133 4.61978 16.6133 5.74695V12.8303" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <div>
        <h4 class="font-bold text-16px text-primary mb-1">سلتك فارغة!</h4>
        <p class="font-medium text-13px text-primary/50">ابدأ بإضافة دورات لتجدها هنا.</p>
    </div>

    @if(!empty($cartDiscount) && !empty($cartDiscount->code))
        <div class="w-full bg-secondary/5 border border-secondary/20 rounded-10px px-4 py-3">
            <p class="font-medium text-12px text-primary/60 mb-1">كوبون خصم خاص لك:</p>
            <p class="font-bold text-15px text-secondary tracking-wider">{{ $cartDiscount->code }}</p>
        </div>
    @endif

    <a href="{{ route('landing.v1.courses') }}"
       class="btn btn-primary btn-block font-medium text-14px"
       data-overlay="#cart-drawer">
        استكشف الدورات
    </a>
</div>
    