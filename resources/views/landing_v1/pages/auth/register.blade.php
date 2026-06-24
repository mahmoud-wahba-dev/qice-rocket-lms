@extends('landing_v1.layouts.app')

@section('content')
@php($landingImg = asset('assets/landing_v1/img/auth'))
<main class="bg-[#F5F8F9] overflow-hidden">

    <header class="m-16 rounded-20px bg-white px-9 py-15 shadow">
        <div class="">
            <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-20 gap-4">
                <div class="">
                    <div>
                        <h1 class="font-semibold text-36px text-primary mb-2">ابدأ رحلتك المهنية الآن</h1>
                        @if (!empty($authIntentMessage))
                            <p class="font-medium text-16px text-primary/80 mb-4 rounded-8px bg-primary/5 border border-primary/15 px-4 py-3">
                                {{ $authIntentMessage }}
                            </p>
                        @endif
                        <p class="font-medium text-base text-primary mb-9">خطوة واحدة تفصلك عن الوصول لمكتبة واسعة من
                            الخبرات الميدانية</p>

                        <div>
                            <nav class="tabs grid grid-cols-3 flex-nowrap h-20  space-x-1 rtl:space-x-reverse p-1 rounded-10px border mb-6 border-[#CCCCCC] h-16"
                                aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                                <button type="button"
                                    class="btn btn-text  active-tab:bg-primary h-full font-medium text-20px text-primary active-tab:text-white hover:text-primary active hover:bg-primary/20"
                                    id="tabs-pill-icon-item-1" data-tab="#tabs-pill-icon-1"
                                    aria-controls="tabs-pill-icon-1" role="tab" aria-selected="false">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="text-current"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_362_6135)">
                                            <path
                                                d="M19.8488 3.2395L10.2534 0.0410395C10.0893 -0.0136798 9.9118 -0.0136798 9.74765 0.0410395L0.152281 3.2395C-0.00693691 3.29257 -0.145418 3.3944 -0.243544 3.53056C-0.341671 3.66672 -0.394467 3.8303 -0.394455 3.99813V11.9943C-0.394455 12.2063 -0.31021 12.4097 -0.160253 12.5597C-0.0102966 12.7096 0.193088 12.7939 0.405159 12.7939C0.61723 12.7939 0.820615 12.7096 0.970572 12.5597C1.12053 12.4097 1.20477 12.2063 1.20477 11.9943V5.1076L4.56215 6.22606C3.67014 7.66716 3.38649 9.40336 3.77347 11.0534C4.16045 12.7035 5.18644 14.1326 6.62616 15.0268C4.82703 15.7325 3.27178 17.0089 2.13433 18.754C2.07517 18.8419 2.03408 18.9407 2.01344 19.0447C1.99281 19.1486 1.99304 19.2556 2.01412 19.3595C2.03521 19.4633 2.07672 19.562 2.13626 19.6496C2.19579 19.7373 2.27216 19.8122 2.36092 19.8701C2.44968 19.928 2.54906 19.9677 2.65328 19.9868C2.75751 20.006 2.8645 20.0042 2.96804 19.9817C3.07158 19.9591 3.1696 19.9162 3.2564 19.8554C3.34321 19.7946 3.41706 19.7172 3.47368 19.6276C4.97995 17.3167 7.3588 15.9923 10.0005 15.9923C12.6423 15.9923 15.0211 17.3167 16.5274 19.6276C16.6447 19.8019 16.8258 19.923 17.0317 19.9648C17.2376 20.0066 17.4517 19.9657 17.6276 19.851C17.8036 19.7362 17.9273 19.5568 17.9721 19.3515C18.0168 19.1462 17.979 18.9316 17.8667 18.754C16.7293 17.0089 15.168 15.7325 13.3749 15.0268C14.8132 14.1326 15.8383 12.7045 16.2252 11.0556C16.6121 9.40675 16.3293 7.67174 15.4389 6.23105L19.8488 4.76176C20.008 4.70872 20.1466 4.6069 20.2447 4.47074C20.3429 4.33458 20.3957 4.17098 20.3957 4.00313C20.3957 3.83527 20.3429 3.67168 20.2447 3.53552C20.1466 3.39936 20.008 3.29754 19.8488 3.24449V3.2395ZM14.7982 9.59543C14.7984 10.3539 14.6188 11.1017 14.2741 11.7773C13.9294 12.4529 13.4294 13.0372 12.8151 13.4821C12.2008 13.9271 11.4898 14.2201 10.7404 14.3371C9.99101 14.4541 9.22454 14.3917 8.5039 14.1551C7.78326 13.9185 7.12899 13.5144 6.59476 12.9759C6.06054 12.4375 5.66159 11.7801 5.43064 11.0576C5.1997 10.3351 5.14334 9.56818 5.2662 8.8197C5.38905 8.07123 5.68762 7.36255 6.13739 6.7518L9.74765 7.95122C9.9118 8.00594 10.0893 8.00594 10.2534 7.95122L13.8637 6.7518C14.4711 7.57543 14.7986 8.57203 14.7982 9.59543ZM10.0005 6.35399L2.93394 3.99813L10.0005 1.64227L17.0671 3.99813L10.0005 6.35399Z"
                                                fill="currentColor" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_362_6135">
                                                <rect width="20" height="20" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>



                                    <span class="hidden sm:inline">مُتدرب</span>
                                </button>
                                <button type="button"
                                    class="btn btn-text active-tab:bg-primary h-full font-medium text-20px text-primary active-tab:text-white hover:text-primary  hover:bg-primary/20"
                                    id="tabs-pill-icon-item-2" data-tab="#tabs-pill-icon-2"
                                    aria-controls="tabs-pill-icon-2" role="tab" aria-selected="false">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="text-current"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_362_6140)">
                                            <path
                                                d="M0 -0.140625H20V4.7316H17.7778V2.0816H2.22222V12.0816H7.03667V14.3038H0V-0.140625Z"
                                                fill="currentColor" />
                                            <path
                                                d="M10.7593 7.95908C11.7304 8.98686 12.5582 9.86241 14.4438 9.86241H16.666C17.2554 9.86241 17.8206 10.0965 18.2373 10.5133C18.6541 10.93 18.8882 11.4953 18.8882 12.0846V13.1957C18.8882 13.5858 18.7855 13.969 18.5905 14.3068C18.3954 14.6446 18.1149 14.9252 17.7771 15.1202V20.9735H14.4438V12.0846C11.536 12.0846 10.0482 10.4735 9.06598 9.41241C8.93281 9.26613 8.79686 9.1224 8.6582 8.9813L10.2293 7.41019C10.4138 7.59538 10.5904 7.77834 10.7593 7.95908ZM17.7771 7.08464C17.7771 7.52666 17.6015 7.95059 17.2889 8.26315C16.9764 8.57571 16.5525 8.7513 16.1104 8.7513C15.6684 8.7513 15.2445 8.57571 14.9319 8.26315C14.6194 7.95059 14.4438 7.52666 14.4438 7.08464C14.4438 6.64261 14.6194 6.21868 14.9319 5.90612C15.2445 5.59356 15.6684 5.41797 16.1104 5.41797C16.5525 5.41797 16.9764 5.59356 17.2889 5.90612C17.6015 6.21868 17.7771 6.64261 17.7771 7.08464Z"
                                                fill="currentColor" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_362_6140">
                                                <rect width="20" height="20" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>



                                    <span class="hidden sm:inline">مدرب</span>
                                </button>


                                <button type="button"
                                    class="btn btn-text active-tab:bg-primary h-full font-medium text-20px text-primary active-tab:text-white hover:text-primary  hover:bg-primary/20"
                                    id="tabs-pill-icon-item-3" data-tab="#tabs-pill-icon-3"
                                    aria-controls="tabs-pill-icon-3" role="tab" aria-selected="false">
                                    <svg width="17" height="19" viewBox="0 0 17 19" fill="none" class="text-current"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M16 17.8106H1M3.5 9.47725V14.4773M8.5 9.47725V14.4773M13.5 9.47725V14.4773M1 5.31059V6.14392H16V5.31059L8.5 1.14392L1 5.31059Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="square" />
                                    </svg>



                                    <span class="hidden sm:inline">مؤسسة</span>
                                </button>
                            </nav>


                            <div class="mt-3">
                                <div id="tabs-pill-icon-1" role="tabpanel" aria-labelledby="tabs-pill-icon-item-1">
                                    <form method="POST" action="/register" class="needs-validation peer grid gap-y-5" novalidate>
                                        @csrf
                                        <input type="hidden" name="account_type" value="user">

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="studentFullName">الاسم كامل</label>
                                            <input id="studentFullName" name="full_name" type="text" value="{{ old('full_name') }}" placeholder="الاسم كامل"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('full_name') border-red-500 @enderror"
                                                required minlength="3" />
                                            @error('full_name')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى إدخال اسم صحيح (3 أحرف على الأقل).</span>
                                            @enderror
                                              <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="studentEmail">البريد الالكتروني</label>
                                            <input id="studentEmail" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('email') border-red-500 @enderror"
                                                required />
                                            @error('email')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى إدخال بريد الكتروني صحيح.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4">الجوال</label>
                                            <div class="grid grid-cols-3 gap-2" dir="ltr">
                                                <div class="col-span-1">
                                                    <select name="country_code" class="select bg-f7 h-16 rounded-7px w-full text-primary text-center font-medium whitespace-normal" required>
                                                        @foreach(getCountriesMobileCode() as $country => $code)
                                                            <option value="{{ $code }}" @if($code == old('country_code')) selected @endif>{{ $code }} ({{ $country }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <input id="studentPhone" name="mobile" type="tel" value="{{ old('mobile') }}" placeholder="05xxxxxxxx"
                                                        class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('mobile') border-red-500 @enderror"
                                                        autocomplete="tel" required />
                                                </div>
                                            </div>
                                            @error('mobile')
                                                <span class="text-14px text-secondary block mt-1" dir="rtl">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary text-right block mt-1" dir="rtl">يرجى إدخال رقم الجوال.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70 text-right block mt-1" dir="rtl">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="studentPassword">كلمة المرور</label>
                                            <div class="relative">
                                                <input id="studentPassword" name="password" type="password" placeholder="كلمة المرور"
                                                    class="input bg-f7 h-16 rounded-7px w-full text-primary text-start pe-12 @error('password') border-red-500 @enderror"
                                                    required minlength="6" />
                                                <button type="button" class="password-toggle size-5 absolute end-3 top-1/2 -translate-y-1/2 text-primary/60 hover:text-primary"
                                                    data-target="#studentPassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                                    <span class="icon-[tabler--eye] size-5 password-toggle-show"></span>
                                                    <span class="icon-[tabler--eye-off] size-5 hidden password-toggle-hide"></span>
                                                </button>
                                            </div>
                                            @error('password')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">كلمة المرور يجب أن تكون 6 أحرف على الأقل.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="studentConfirmPassword">تأكيد كلمة المرور</label>
                                            <div class="relative">
                                                <input id="studentConfirmPassword" name="password_confirmation" type="password" placeholder="تأكيد كلمة المرور"
                                                    class="input bg-f7 h-16 rounded-7px w-full text-primary text-start pe-12 @error('password_confirmation') border-red-500 @enderror"
                                                    required />
                                                <button type="button" class="password-toggle size-5 absolute end-3 top-1/2 -translate-y-1/2 text-primary/60 hover:text-primary"
                                                    data-target="#studentConfirmPassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                                    <span class="icon-[tabler--eye] size-5 password-toggle-show"></span>
                                                    <span class="icon-[tabler--eye-off] size-5 hidden password-toggle-hide"></span>
                                                </button>
                                            </div>
                                            @error('password_confirmation')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى تأكيد كلمة المرور.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        @if(!empty(getReferralSettings()) and getReferralSettings()['status'])
                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="studentReferralCode">رمز الإحالة (اختياري)</label>
                                            <input id="studentReferralCode" name="referral_code" type="text" value="{{ Cookie::get('referral_code') ?? old('referral_code') }}" placeholder="رمز الإحالة"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('referral_code') border-red-500 @enderror" />
                                            @error('referral_code')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        @endif

                                        <div class="mt-4">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="term" value="1" id="studentTerm" class="checkbox checkbox-primary size-6 rounded-md border-gray-300 text-primary focus:ring-primary" required {{ old('term') == '1' ? 'checked' : '' }}>
                                                <label for="studentTerm" class="font-medium text-18px text-primary cursor-pointer select-none">
                                                    أوافق على <a href="/pages/terms" target="_blank" class="link font-bold text-gold">الشروط والأحكام</a>
                                                </label>
                                            </div>
                                            @error('term')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary block mt-1">يجب الموافقة على الشروط والأحكام.</span>
                                            @enderror
                                        </div>

                                        <!-- Submit button -->
                                        <div class="mt-4">
                                            <button type="submit" name="submitButton"
                                                class="btn btn-primary btn-block h-16 rounded-8px font-semibold text-24px">انشاء حساب</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="tabs-pill-icon-2" class="hidden" role="tabpanel" aria-labelledby="tabs-pill-icon-item-2">
                                    <form method="POST" action="/register" class="needs-validation peer grid gap-y-5" novalidate>
                                        @csrf
                                        <input type="hidden" name="account_type" value="teacher">

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="traineeFullName">الاسم كامل</label>
                                            <input id="traineeFullName" name="full_name" type="text" value="{{ old('full_name') }}" placeholder="الاسم كامل"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('full_name') border-red-500 @enderror"
                                                required minlength="3" />
                                            @error('full_name')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى إدخال اسم صحيح (3 أحرف على الأقل).</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="traineeEmail">البريد الالكتروني</label>
                                            <input id="traineeEmail" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('email') border-red-500 @enderror"
                                                required />
                                            @error('email')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى إدخال بريد الكتروني صحيح.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4">الجوال</label>
                                            <div class="grid grid-cols-3 gap-2" dir="ltr">
                                                <div class="col-span-1">
                                                    <select name="country_code" class="select bg-f7 h-16 rounded-7px w-full text-primary text-center font-medium whitespace-normal" required>
                                                        @foreach(getCountriesMobileCode() as $country => $code)
                                                            <option value="{{ $code }}" @if($code == old('country_code')) selected @endif>{{ $code }} ({{ $country }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <input id="traineePhone" name="mobile" type="tel" value="{{ old('mobile') }}" placeholder="05xxxxxxxx"
                                                        class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('mobile') border-red-500 @enderror"
                                                        autocomplete="tel" required />
                                                </div>
                                            </div>
                                            @error('mobile')
                                                <span class="text-14px text-secondary block mt-1" dir="rtl">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary text-right block mt-1" dir="rtl">يرجى إدخال رقم الجوال.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70 text-right block mt-1" dir="rtl">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="traineePassword">كلمة المرور</label>
                                            <div class="relative">
                                                <input id="traineePassword" name="password" type="password" placeholder="كلمة المرور"
                                                    class="input bg-f7 h-16 rounded-7px w-full text-primary text-start pe-12 @error('password') border-red-500 @enderror"
                                                    required minlength="6" />
                                                <button type="button" class="password-toggle size-5 absolute end-3 top-1/2 -translate-y-1/2 text-primary/60 hover:text-primary"
                                                    data-target="#traineePassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                                    <span class="icon-[tabler--eye] size-5 password-toggle-show"></span>
                                                    <span class="icon-[tabler--eye-off] size-5 hidden password-toggle-hide"></span>
                                                </button>
                                            </div>
                                            @error('password')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">كلمة المرور يجب أن تكون 6 أحرف على الأقل.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="traineeConfirmPassword">تأكيد كلمة المرور</label>
                                            <div class="relative">
                                                <input id="traineeConfirmPassword" name="password_confirmation" type="password" placeholder="تأكيد كلمة المرور"
                                                    class="input bg-f7 h-16 rounded-7px w-full text-primary text-start pe-12 @error('password_confirmation') border-red-500 @enderror"
                                                    required />
                                                <button type="button" class="password-toggle size-5 absolute end-3 top-1/2 -translate-y-1/2 text-primary/60 hover:text-primary"
                                                    data-target="#traineeConfirmPassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                                    <span class="icon-[tabler--eye] size-5 password-toggle-show"></span>
                                                    <span class="icon-[tabler--eye-off] size-5 hidden password-toggle-hide"></span>
                                                </button>
                                            </div>
                                            @error('password_confirmation')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى تأكيد كلمة المرور.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        @if(!empty(getReferralSettings()) and getReferralSettings()['status'])
                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="traineeReferralCode">رمز الإحالة (اختياري)</label>
                                            <input id="traineeReferralCode" name="referral_code" type="text" value="{{ Cookie::get('referral_code') ?? old('referral_code') }}" placeholder="رمز الإحالة"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('referral_code') border-red-500 @enderror" />
                                            @error('referral_code')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        @endif

                                        <div class="mt-4">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="term" value="1" id="traineeTerm" class="checkbox checkbox-primary size-6 rounded-md border-gray-300 text-primary focus:ring-primary" required {{ old('term') == '1' ? 'checked' : '' }}>
                                                <label for="traineeTerm" class="font-medium text-18px text-primary cursor-pointer select-none">
                                                    أوافق على <a href="/pages/terms" target="_blank" class="link font-bold text-gold">الشروط والأحكام</a>
                                                </label>
                                            </div>
                                            @error('term')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary block mt-1">يجب الموافقة على الشروط والأحكام.</span>
                                            @enderror
                                        </div>

                                        <!-- Submit button -->
                                        <div class="mt-4">
                                            <button type="submit" name="submitButton"
                                                class="btn btn-primary btn-block h-16 rounded-8px font-semibold text-24px">انشاء حساب</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="tabs-pill-icon-3" class="hidden" role="tabpanel" aria-labelledby="tabs-pill-icon-item-3">
                                    <form method="POST" action="/register" class="needs-validation peer grid gap-y-5" novalidate>
                                        @csrf
                                        <input type="hidden" name="account_type" value="organization">

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="orgFullName">اسم المؤسسة</label>
                                            <input id="orgFullName" name="full_name" type="text" value="{{ old('full_name') }}" placeholder="اسم المؤسسة"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('full_name') border-red-500 @enderror"
                                                required minlength="3" />
                                            @error('full_name')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى إدخال اسم مؤسسة صحيح (3 أحرف على الأقل).</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="orgEmail">البريد الالكتروني</label>
                                            <input id="orgEmail" name="email" type="email" value="{{ old('email') }}" placeholder="org@example.com"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('email') border-red-500 @enderror"
                                                required />
                                            @error('email')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى إدخال بريد الكتروني صحيح.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4">الجوال</label>
                                            <div class="grid grid-cols-3 gap-2" dir="ltr">
                                                <div class="col-span-1">
                                                    <select name="country_code" class="select bg-f7 h-16 rounded-7px w-full text-primary text-center font-medium whitespace-normal" required>
                                                        @foreach(getCountriesMobileCode() as $country => $code)
                                                            <option value="{{ $code }}" @if($code == old('country_code')) selected @endif>{{ $code }} ({{ $country }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <input id="orgPhone" name="mobile" type="tel" value="{{ old('mobile') }}" placeholder="05xxxxxxxx"
                                                        class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('mobile') border-red-500 @enderror"
                                                        autocomplete="tel" required />
                                                </div>
                                            </div>
                                            @error('mobile')
                                                <span class="text-14px text-secondary block mt-1" dir="rtl">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary text-right block mt-1" dir="rtl">يرجى إدخال رقم الجوال.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70 text-right block mt-1" dir="rtl">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="orgPassword">كلمة المرور</label>
                                            <div class="relative">
                                                <input id="orgPassword" name="password" type="password" placeholder="كلمة المرور"
                                                    class="input bg-f7 h-16 rounded-7px w-full text-primary text-start pe-12 @error('password') border-red-500 @enderror"
                                                    required minlength="6" />
                                                <button type="button" class="password-toggle size-5 absolute end-3 top-1/2 -translate-y-1/2 text-primary/60 hover:text-primary"
                                                    data-target="#orgPassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                                    <span class="icon-[tabler--eye] size-5 password-toggle-show"></span>
                                                    <span class="icon-[tabler--eye-off] size-5 hidden password-toggle-hide"></span>
                                                </button>
                                            </div>
                                            @error('password')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">كلمة المرور يجب أن تكون 6 أحرف على الأقل.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="orgConfirmPassword">تأكيد كلمة المرور</label>
                                            <div class="relative">
                                                <input id="orgConfirmPassword" name="password_confirmation" type="password" placeholder="تأكيد كلمة المرور"
                                                    class="input bg-f7 h-16 rounded-7px w-full text-primary text-start pe-12 @error('password_confirmation') border-red-500 @enderror"
                                                    required />
                                                <button type="button" class="password-toggle size-5 absolute end-3 top-1/2 -translate-y-1/2 text-primary/60 hover:text-primary"
                                                    data-target="#orgConfirmPassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                                    <span class="icon-[tabler--eye] size-5 password-toggle-show"></span>
                                                    <span class="icon-[tabler--eye-off] size-5 hidden password-toggle-hide"></span>
                                                </button>
                                            </div>
                                            @error('password_confirmation')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary">يرجى تأكيد كلمة المرور.</span>
                                            @enderror
                                            <span class="success-message text-14px text-primary/70">تم بنجاح.</span>
                                        </div>

                                        @if(!empty(getReferralSettings()) and getReferralSettings()['status'])
                                        <div>
                                            <label class="label label-text font-medium text-24px text-primary mb-4" for="orgReferralCode">رمز الإحالة (اختياري)</label>
                                            <input id="orgReferralCode" name="referral_code" type="text" value="{{ Cookie::get('referral_code') ?? old('referral_code') }}" placeholder="رمز الإحالة"
                                                class="input bg-f7 h-16 rounded-7px w-full text-primary text-start @error('referral_code') border-red-500 @enderror" />
                                            @error('referral_code')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        @endif

                                        <div class="mt-4">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="term" value="1" id="orgTerm" class="checkbox checkbox-primary size-6 rounded-md border-gray-300 text-primary focus:ring-primary" required {{ old('term') == '1' ? 'checked' : '' }}>
                                                <label for="orgTerm" class="font-medium text-18px text-primary cursor-pointer select-none">
                                                    أوافق على <a href="/pages/terms" target="_blank" class="link font-bold text-gold">الشروط والأحكام</a>
                                                </label>
                                            </div>
                                            @error('term')
                                                <span class="text-14px text-secondary block mt-1">{{ $message }}</span>
                                            @else
                                                <span class="error-message text-14px text-secondary block mt-1">يجب الموافقة على الشروط والأحكام.</span>
                                            @enderror
                                        </div>

                                        <!-- Submit button -->
                                        <div class="mt-4">
                                            <button type="submit" name="submitButton"
                                                class="btn btn-primary btn-block h-16 rounded-8px font-semibold text-24px">انشاء حساب</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-7">
                                <p class="font-medium text-20px text-[#979797] center">
                                    ليس لديك حساب ؟
                                    <a class="link link-animated font-semibold text-22px text-primary px-2"
                                        href="{{ route('landing.v1.register') }}">انشاء حساب</a>
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
                <x-landing_v1::auth-slider />
            </div>
        </div>
    </header>


</main>
@endsection
@push('scripts')
<style>
    .error-message, .success-message {
        display: none !important;
    }
    .needs-validation .error-message,
    .needs-validation .text-secondary.block.mt-1 {
        color: #E25C5C !important;
    }
    .validate .input:invalid ~ .error-message,
    .validate .select:invalid ~ .error-message,
    .validate .checkbox:invalid ~ .error-message {
        display: block !important;
    }
    .validate > div:has(.input:invalid) > .error-message,
    .validate > div:has(.select:invalid) > .error-message,
    .validate > div:has(.checkbox:invalid) > .error-message {
        display: block !important;
    }
    .validate .validation-has-error > .error-message {
        display: block !important;
    }
    .validate .input:valid ~ .success-message,
    .validate .select:valid ~ .success-message {
        display: block !important;
    }
    .validate > div:has(.input:valid):not(:has(.input:invalid, .select:invalid, .checkbox:invalid)) > .success-message,
    .validate > div:has(.select:valid):not(:has(.input:invalid, .select:invalid, .checkbox:invalid)) > .success-message {
        display: block !important;
    }
    .validate .validation-is-valid > .success-message {
        display: block !important;
    }
    .validate .input:invalid, .validate .select:invalid {
        border: 2px solid #E25C5C !important;
    }
    .validate .input:valid, .validate .select:valid {
        border: 2px solid #5CE284 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.needs-validation');

    function getValidationFields(form) {
        return Array.from(form.querySelectorAll('.error-message, .success-message'))
            .map(message => message.parentElement)
            .filter((field, index, fields) => field && fields.indexOf(field) === index);
    }

    function syncValidationState(form) {
        getValidationFields(form).forEach(field => {
            const controls = Array.from(field.querySelectorAll('input, select'));
            const hasInvalidControl = controls.some(control => !control.validity.valid);
            const hasControls = controls.length > 0;

            field.classList.toggle('validation-has-error', hasInvalidControl);
            field.classList.toggle('validation-is-valid', hasControls && !hasInvalidControl);
        });
    }

    Array.from(forms).forEach(form => {
        const passwordInput = form.querySelector('input[name="password"]');
        const confirmInput = form.querySelector('input[name="password_confirmation"]');
        const confirmErrorMessage = confirmInput
            ? confirmInput.closest('div').parentElement.querySelector('.error-message')
            : null;
        const confirmDefaultMessage = confirmErrorMessage ? confirmErrorMessage.textContent : '';

        function validatePasswordMatch() {
            if (passwordInput && confirmInput) {
                if (passwordInput.value !== confirmInput.value) {
                    const message = "كلمتا المرور غير متطابقتين.";
                    confirmInput.setCustomValidity(message);

                    if (confirmErrorMessage) {
                        confirmErrorMessage.textContent = message;
                    }
                } else {
                    confirmInput.setCustomValidity("");

                    if (confirmErrorMessage) {
                        confirmErrorMessage.textContent = confirmDefaultMessage;
                    }
                }
            }
        }

        if (passwordInput && confirmInput) {
            passwordInput.addEventListener('input', () => {
                validatePasswordMatch();
                syncValidationState(form);
            });
            confirmInput.addEventListener('input', () => {
                validatePasswordMatch();
                syncValidationState(form);
            });
        }

        form.querySelectorAll('input, select').forEach(control => {
            control.addEventListener('input', () => syncValidationState(form));
            control.addEventListener('change', () => syncValidationState(form));
        });

        form.addEventListener(
            'submit',
            event => {
                // Re-validate password matching before submission
                validatePasswordMatch();
                syncValidationState(form);

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    const firstInvalidElement = form.querySelector(':invalid');
                    if (firstInvalidElement) {
                        firstInvalidElement.focus();
                    }
                    form.classList.add('validate');
                } else {
                    // Form is valid! Show loading state on the active submit button
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block ltr:mr-3 rtl:ml-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            جاري التحميل...
                        `;
                    }
                }
            },
            false
        );
    });
});
</script>
@endpush
