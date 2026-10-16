@php
    $whatsappUrl = config('landing_v1.whatsapp_url', 'https://api.whatsapp.com/send/?phone=966503976186&text&type=phone_number&app_absent=0');
    $whatsappNumber = config('landing_v1.whatsapp_number', '+966 50 397 6186');
    $whatsappPhoneDisplay = '050 397 6186';
@endphp

<!-- Floating WhatsApp Button With Premium Animations -->
<div id="qiec-floating-whatsapp"
    class="fixed bottom-5 end-5 sm:bottom-7 sm:end-7 z-50 select-none print:hidden pointer-events-auto"
    dir="rtl">
    <a href="{{ $whatsappUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="تواصل معنا عبر واتساب: {{ $whatsappPhoneDisplay }}"
        class="qiec-wa-button group relative flex items-center justify-center p-3 rounded-full text-white shadow-2xl transition-all duration-300 ease-out hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-[#25D366]/40">

        <!-- Outer Animated Ripple Rings -->
        <span class="qiec-wa-ring qiec-wa-ring-1" aria-hidden="true"></span>
        <span class="qiec-wa-ring qiec-wa-ring-2" aria-hidden="true"></span>

        <!-- WhatsApp Icon Badge -->
        <span class="relative flex items-center justify-center size-10 sm:size-11 rounded-full bg-white text-[#25D366] shadow-md shrink-0 transition-transform duration-300 group-hover:rotate-12">
            <svg class="size-6 sm:size-7 qiec-wa-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M17.507 14.307l-.009.075c-2.199-1.096-2.429-1.242-2.713-.815-.166.249-.642.808-.788.969-.146.161-.291.181-.542.06-.252-.12-1.063-.392-2.026-1.25-.748-.667-1.253-1.492-1.4-1.743-.146-.251-.015-.387.106-.507.11-.109.252-.282.378-.423.126-.141.168-.242.252-.403.084-.162.042-.302-.021-.423-.063-.12-0.566-1.365-.776-1.87-.204-.49-.412-.423-.566-.431-.146-.008-.314-.01-.482-.01-.168 0-.441.063-.672.312-.231.251-.881.861-.881 2.101 0 1.24 0.902 2.44 1.028 2.611.126.171 1.773 2.708 4.297 3.797.601.259 1.07.414 1.436.53.604.192 1.154.165 1.588.1 0.485-.072 1.492-.61 1.702-1.2 0.21-.59 0.21-1.096 0.147-1.202-.063-.105-.231-.166-.483-.287z"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 1.892.525 3.662 1.438 5.176L2.05 21.65a.75.75 0 00.916.923l4.633-1.312A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm-8.5 10c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5-3.806 8.5-8.5 8.5a8.463 8.463 0 01-4.32-1.182.75.75 0 00-.573-.082l-3.327.943.914-3.189a.75.75 0 00-.097-.61A8.46 8.46 0 013.5 12z"/>
            </svg>
            <!-- Tiny ping dot on icon -->
            <span class="absolute top-0 end-0 flex size-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full size-3 bg-[#1ebe5d] border-2 border-white"></span>
            </span>
        </span>


    </a>
</div>

<style>
/* Floating Animation for WhatsApp Widget */
#qiec-floating-whatsapp {
    animation: qiecWaFloat 3.8s ease-in-out infinite;
}

.qiec-wa-button {
    background: linear-gradient(135deg, #25D366 0%, #1ebe5d 50%, #128C7E 100%);
    box-shadow: 0 10px 25px -4px rgba(37, 211, 102, 0.45), 0 6px 12px -2px rgba(18, 140, 126, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(8px);
}

.qiec-wa-button:hover {
    box-shadow: 0 14px 30px -4px rgba(37, 211, 102, 0.6), 0 8px 16px -2px rgba(18, 140, 126, 0.45);
}

/* Pulsing concentric ripple rings */
.qiec-wa-ring {
    position: absolute;
    inset: -4px;
    border-radius: 9999px;
    background: rgba(37, 211, 102, 0.4);
    z-index: -1;
    pointer-events: none;
}

.qiec-wa-ring-1 {
    animation: qiecWaRipple 2.8s cubic-bezier(0, 0.2, 0.8, 1) infinite;
}

.qiec-wa-ring-2 {
    animation: qiecWaRipple 2.8s cubic-bezier(0, 0.2, 0.8, 1) infinite;
    animation-delay: 1.4s;
}

/* Icon wiggle periodically */
.qiec-wa-icon {
    animation: qiecWaWiggle 4s ease-in-out infinite;
    transform-origin: center;
}

@keyframes qiecWaFloat {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes qiecWaRipple {
    0% {
        transform: scale(0.95);
        opacity: 0.75;
    }
    60% {
        transform: scale(1.15, 1.35);
        opacity: 0.25;
    }
    100% {
        transform: scale(1.3, 1.6);
        opacity: 0;
    }
}

@keyframes qiecWaWiggle {
    0%, 75%, 100% {
        transform: rotate(0deg);
    }
    80% {
        transform: rotate(-14deg) scale(1.08);
    }
    85% {
        transform: rotate(14deg) scale(1.08);
    }
    90% {
        transform: rotate(-8deg);
    }
    95% {
        transform: rotate(6deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    #qiec-floating-whatsapp,
    .qiec-wa-ring,
    .qiec-wa-icon {
        animation: none !important;
    }
}
</style>
