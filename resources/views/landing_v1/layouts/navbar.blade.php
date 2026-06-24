<nav class="fixed inset-x-0 top-0 z-50 w-full">
    <div
        class="navbar rounded-box flex w-full items-center justify-between gap-2 shadow-[0px_4px_34.5px_-3px_#0000000D] mx-auto [@media(min-width:1600px)]:container">
    <div class="navbar-start max-xl:w-1/4">
        <a class="link link-neutral text-xl font-semibold no-underline" href="{{ route('landing.v1.index') }}">
            <img src="{{ asset('assets/landing_v1/logo_nav.svg') }}" alt="Logo" class="h-16">
        </a>
    </div>
    <div class="navbar-center max-md:hidden">
        <ul class="menu menu-horizontal p-0 font-medium">
            <li><a class="font-medium text-15px text-[#3D455D] " href="{{ route('landing.v1.index') }}">الرئيسية</a>
            </li>
            <li><a class="font-medium text-15px text-[#3D455D] " href="{{ route('landing.v1.workshops') }}">دورات مجانية</a></li>
       
            <li><a class="font-medium text-15px text-[#3D455D] "
                    href="{{ route('landing.v1.instructors') }}">المدربين</a></li>
            <li><a class="font-medium text-15px text-[#3D455D] " href="{{ route('landing.v1.courses-paid') }}">الدورات المعتمدة</a>
            </li>
        </ul>
    </div> 
    
    <div class="navbar-end items-center   gap-4 max-xl:w-fit">
        <div class="dropdown relative inline-flex md:hidden rtl:[--placement:bottom-end]">
            <button id="dropdown-default" type="button"
                class="dropdown-toggle btn btn-text bg-card-text text-white btn-square" aria-haspopup="menu"
                aria-expanded="false" aria-label="Dropdown">
                <span class="icon-[tabler--menu-2] dropdown-open:hidden size-5"></span>
                <span class="icon-[tabler--x] dropdown-open:block hidden size-5"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical"
                aria-labelledby="dropdown-default">
                <li><a class="dropdown-item font-medium text-15px text-[#3D455D]"
                        href="{{ route('landing.v1.index') }}">الرئيسية</a></li>
                {{-- <li><a class="dropdown-item font-medium text-15px text-[#3D455D]"
                        href="{{ route('landing.v1.about') }}">من نحن</a></li> --}}
                <li><a class="dropdown-item font-medium text-15px text-[#3D455D]"
                        href="{{ route('landing.v1.workshops') }}">دورات مجانية</a></li>
                <li><a class="dropdown-item font-medium text-15px text-[#3D455D]"
                        href="{{ route('landing.v1.courses-paid') }}">الدورات المعتمدة</a></li>
                <li><a class="dropdown-item font-medium text-15px text-[#3D455D]"
                        href="{{ route('landing.v1.instructors') }}">المدربين</a></li>
                {{-- <li><a class="dropdown-item font-medium text-15px text-[#3D455D]"
                        href="{{ route('landing.v1.contact') }}">تواصل معنا</a></li> --}}
            </ul>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-4">
                {{-- Cart Button with Badge --}}
                <button id="cart-drawer-btn" type="button" class="btn btn-text max-xl:px-1 cart-icon relative"
                    aria-haspopup="dialog" aria-expanded="false" aria-controls="cart-drawer"
                    data-overlay="#cart-drawer">
                    <svg width="20" height="20" viewBox="0 0 25 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M4.33074 8.58362H20.3943C20.8028 8.58357 21.2065 8.67186 21.5777 8.84244C21.949 9.01302 22.2789 9.26186 22.5449 9.57189C22.8109 9.88192 23.0068 10.2458 23.119 10.6386C23.2312 11.0314 23.2572 11.4438 23.1951 11.8476L21.4172 23.3963C21.2628 24.3999 20.7542 25.3151 19.9835 25.9762C19.2127 26.6373 18.2308 27.0006 17.2153 27.0003H7.50833C6.49314 27.0002 5.51151 26.6368 4.74105 25.9757C3.9706 25.3147 3.46222 24.3997 3.30791 23.3963L1.52999 11.8476C1.4679 11.4438 1.49385 11.0314 1.60607 10.6386C1.7183 10.2458 1.91414 9.88192 2.18016 9.57189C2.44619 9.26186 2.77612 9.01302 3.14732 8.84244C3.51852 8.67186 3.92222 8.58357 4.33074 8.58362Z"
                            stroke="#3D455D" stroke-width="2.994" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M8.11328 12.8303V5.74695C8.11328 4.61978 8.56105 3.53877 9.35808 2.74174C10.1551 1.94471 11.2361 1.49695 12.3633 1.49695C13.4905 1.49695 14.5715 1.94471 15.3685 2.74174C16.1655 3.53877 16.6133 4.61978 16.6133 5.74695V12.8303"
                            stroke="#3D455D" stroke-width="2.994" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{-- Badge (JS-controlled) --}}
                    <span id="cart-badge"
                        class="absolute -top-1 -start-1 hidden items-center justify-center size-5 rounded-full bg-blue text-white text-[13px] font-bold leading-none">0</span>
                </button>
            </div>

            @auth
                {{-- ── User dropdown ── --}}
                <div class="dropdown relative inline-flex rtl:[--placement:bottom-end]">
                    <button id="user-dropdown-toggle" type="button"
                        class="dropdown-toggle flex items-center gap-2 btn btn-text h-12 px-2 hover:bg-primary/5 rounded-10px transition"
                        aria-haspopup="menu" aria-expanded="false" aria-label="User menu">

                        {{-- Avatar --}}
                        <div class="size-9 rounded-full overflow-hidden bg-gold flex items-center justify-center shrink-0 border-2 border-gold/30">
                            <img src="{{ auth()->user()->getAvatar() }}"
                                 alt="{{ auth()->user()->full_name }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <span class="hidden w-full h-full items-center justify-center font-bold text-14px text-white bg-gold">
                                {{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}
                            </span>
                        </div>

                        {{-- Name --}}
                        <span class="max-md:hidden font-semibold text-14px text-primary">{{ auth()->user()->full_name }}</span>
                        <span class="icon-[tabler--chevron-down] size-4 text-primary/60 max-md:hidden dropdown-open:rotate-180 transition-transform duration-200"></span>
                    </button>

                    {{-- Dropdown menu --}}
                    <div class="dropdown-menu dropdown-open:opacity-100 hidden w-64 p-0 rounded-12px border border-gray-100 shadow-xl bg-white overflow-hidden"
                        role="menu" aria-orientation="vertical" aria-labelledby="user-dropdown-toggle">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 px-4 py-4 bg-primary/5 border-b border-gray-100">
                            <div class="size-11 rounded-full overflow-hidden bg-gold flex items-center justify-center shrink-0">
                                <img src="{{ auth()->user()->getAvatar() }}"
                                     alt="{{ auth()->user()->full_name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                <span class="hidden w-full h-full items-center justify-center font-bold text-16px text-white bg-gold">
                                    {{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-bold text-15px text-primary">{{ auth()->user()->full_name }}</p>
                                <p class="font-medium text-12px text-primary/50">{{ auth()->user()->role->caption ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Menu items --}}
                        <ul class="py-2">
                            <li>
                                <a href="{{ auth()->user()->isAdmin() ? getAdminPanelUrl('/') : '/panel' }}"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-primary hover:bg-primary/5 transition">
                                    <span class="icon-[tabler--layout-dashboard] size-5 text-primary/50 shrink-0"></span>
                                    لوحة التحكم
                                </a>
                            </li>
                            <li>
                                <a href="/panel/notifications"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-primary hover:bg-primary/5 transition">
                                    <span class="icon-[tabler--bell] size-5 text-primary/50 shrink-0"></span>
                                    الاشعارات
                                </a>
                            </li>
                            <li>
                                <a href="/panel/courses"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-primary hover:bg-primary/5 transition">
                                    <span class="icon-[tabler--books] size-5 text-primary/50 shrink-0"></span>
                                    فصولي
                                </a>
                            </li>

                            <li class="border-t border-gray-100 mt-1 pt-1">
                                <a href="/panel/supports"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-primary hover:bg-primary/5 transition">
                                    <span class="icon-[tabler--help-circle] size-5 text-primary/50 shrink-0"></span>
                                    الدعم
                                </a>
                            </li>
                            <li>
                                <a href="{{ auth()->user()->getProfileUrl() }}"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-primary hover:bg-primary/5 transition">
                                    <span class="icon-[tabler--user] size-5 text-primary/50 shrink-0"></span>
                                    الملف الشخصي
                                </a>
                            </li>
                            <li>
                                <a href="/panel/setting"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-primary hover:bg-primary/5 transition">
                                    <span class="icon-[tabler--settings] size-5 text-primary/50 shrink-0"></span>
                                    الاعدادات
                                </a>
                            </li>

                            <li class="border-t border-gray-100 mt-1 pt-1">
                                <a href="/logout"
                                    class="dropdown-item flex items-center gap-3 px-4 py-2.5 font-medium text-14px text-red-500 hover:bg-red-50 transition">
                                    <span class="icon-[tabler--logout] size-5 text-red-400 shrink-0"></span>
                                    تسجيل الخروج
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @else
                <a class="btn max-xl:btn-square btn-primary font-medium text-17px h-12"
                    href="{{ route('landing.v1.login') }}"
                    aria-label="تسجيل الدخول">
                    <span class="icon-[tabler--login] size-5 xl:hidden"></span>
                    <span class="max-xl:hidden font-medium text-15px">تسجيل الدخول</span>
                </a>
            @endauth
        </div>
    </div>
</nav>


{{-- ============================================================
     CART DRAWER — opens from the right (visually left in RTL)
     Uses FlyonUI drawer-start which in RTL = left side
     ============================================================ --}}
<div id="cart-drawer"
    class="overlay overlay-open:translate-x-0 drawer drawer-start cart-drawer hidden"
    role="dialog" tabindex="-1">

    {{-- Header --}}
    <div class="drawer-header cart-drawer__header">
        <div class="flex items-center gap-3">
            <span class="cart-drawer__header-icon">
                <svg width="20" height="20" viewBox="0 0 25 29" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.33074 8.58362H20.3943C20.8028 8.58357 21.2065 8.67186 21.5777 8.84244C21.949 9.01302 22.2789 9.26186 22.5449 9.57189C22.8109 9.88192 23.0068 10.2458 23.119 10.6386C23.2312 11.0314 23.2572 11.4438 23.1951 11.8476L21.4172 23.3963C21.2628 24.3999 20.7542 25.3151 19.9835 25.9762C19.2127 26.6373 18.2308 27.0006 17.2153 27.0003H7.50833C6.49314 27.0002 5.51151 26.6368 4.74105 25.9757C3.9706 25.3147 3.46222 24.3997 3.30791 23.3963L1.52999 11.8476C1.4679 11.4438 1.49385 11.0314 1.60607 10.6386C1.7183 10.2458 1.91414 9.88192 2.18016 9.57189C2.44619 9.26186 2.77612 9.01302 3.14732 8.84244C3.51852 8.67186 3.92222 8.58357 4.33074 8.58362Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.11328 12.8303V5.74695C8.11328 4.61978 8.56105 3.53877 9.35808 2.74174C10.1551 1.94471 11.2361 1.49695 12.3633 1.49695C13.4905 1.49695 14.5715 1.94471 15.3685 2.74174C16.1655 3.53877 16.6133 4.61978 16.6133 5.74695V12.8303" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <h3 class="cart-drawer__title">سلة التسوق</h3>
            <span id="cart-drawer-count-badge" class="cart-drawer__count-badge hidden">0</span>
        </div>
        <button type="button" class="cart-drawer__close btn btn-text btn-circle btn-sm"
            aria-label="إغلاق" data-overlay="#cart-drawer">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    {{-- Body — loaded via AJAX --}}
    <div class="drawer-body cart-drawer__body" id="cart-drawer-body">
        <div id="cart-drawer-loading" class="cart-drawer-loading">
            <span class="icon-[tabler--loader-2] size-8 animate-spin text-primary/40"></span>
            <p class="font-medium text-14px text-primary/50">جاري التحميل...</p>
        </div>
    </div>

    {{-- Footer — subtotal + actions --}}
    <div class="drawer-footer cart-drawer__footer" id="cart-drawer-footer">
        <div class="cart-drawer__subtotal" id="cart-drawer-subtotal-row">
            <span class="cart-drawer__subtotal-label">المجموع الفرعي</span>
            <span id="cart-drawer-subtotal" class="cart-drawer__subtotal-value">0</span>
        </div>
        <div class="cart-drawer__actions">
            <button type="button" class="btn btn-outline btn-primary flex-1 font-semibold text-14px h-12"
                data-overlay="#cart-drawer">
                متابعة التسوق
            </button>
            <a href="{{ route('landing.v1.cart') }}" id="cart-drawer-checkout-btn"
                class="btn btn-primary flex-1 font-semibold text-14px h-12">
                عرض السلة
            </a>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="cart-toast"
    class="fixed bottom-6 start-6 z-[9999] flex items-center gap-3 bg-white border border-gray-100 shadow-xl rounded-10px px-5 py-4 transition-all duration-300 translate-y-20 opacity-0 pointer-events-none max-w-sm">
    <span id="cart-toast-icon" class="icon-[tabler--circle-check-filled] size-6 text-green-500 shrink-0"></span>
    <div>
        <p id="cart-toast-title" class="font-bold text-14px text-primary"></p>
        <p id="cart-toast-msg" class="font-medium text-12px text-primary/60"></p>
    </div>
    <button onclick="hideCartToast()" class="ms-auto text-primary/40 hover:text-primary transition">
        <span class="icon-[tabler--x] size-4"></span>
    </button>
</div>
