@extends('panel_v1.student.layouts.app')

@section('content')
@php
    $toggleClass = 'peer sr-only';
    $toggleTrack = 'relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full bg-d9 transition peer-checked:bg-[#0FC787] after:absolute after:top-0.5 after:start-0.5 after:size-6 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:after:translate-x-[-1.25rem] rtl:peer-checked:after:translate-x-[-1.25rem]';
    $input = 'input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary';
    $label = 'absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray';
    $card = 'border border-d9 rounded-20px bg-white px-8 py-8';
    $birthday = !empty($authUser->birthday)
        ? \Carbon\Carbon::createFromTimestamp((int) $authUser->birthday, $authUser->timezone ?? config('app.timezone'))->format('Y-m-d')
        : '';
@endphp

<section class="">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">اعدادات الحساب</h1>
            <p class="font-semibold text-24px text-gray">إدارة إعدادات حسابك</p>
        </div>

        <nav class="student-dash-tabs w-[80%] tabs tabs-bordered tabs-lg w-full overflow-x-auto mb-12"
            aria-label="أقسام إعدادات الحساب" role="tablist" aria-orientation="horizontal">
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 active whitespace-nowrap"
                id="settings-tabs-item-1" data-v1-tab="#settings-tabs-1" aria-controls="settings-tabs-1" role="tab"
                aria-selected="true">
                معلومات أساسية
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-2" data-v1-tab="#settings-tabs-2" aria-controls="settings-tabs-2" role="tab"
                aria-selected="false">
                معلومات اضافية
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-3" data-v1-tab="#settings-tabs-3" aria-controls="settings-tabs-3" role="tab"
                aria-selected="false">
                الهوية والمالية
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-4" data-v1-tab="#settings-tabs-4" aria-controls="settings-tabs-4" role="tab"
                aria-selected="false">
                الصور
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-5" data-v1-tab="#settings-tabs-5" aria-controls="settings-tabs-5" role="tab"
                aria-selected="false">
                حول
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-6" data-v1-tab="#settings-tabs-6" aria-controls="settings-tabs-6" role="tab"
                aria-selected="false">
                سجل الدخول
            </button>
        </nav>

        <div>
            <div id="settings-tabs-1" role="tabpanel" aria-labelledby="settings-tabs-item-1">
                <form action="{{ route('panel.v1.student.settings.update') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {{-- Main column --}}
                    <div class="lg:col-span-7 flex flex-col gap-8">
                        <div class="{{ $card }}">
                            <h2 class="font-bold text-24px text-primary mb-8">الحساب والأمان</h2>

                            <div class="space-y-7">
                                <div class="relative">
                                    <label class="{{ $label }}">الاسم</label>
                                    <input type="text" name="full_name" value="{{ old('full_name', $authUser->full_name ?? '') }}"
                                        class="{{ $input }}">
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">البريد الإلكتروني</label>
                                    <input type="email" name="email" value="{{ old('email', $authUser->email ?? '') }}" dir="ltr" class="text-start {{ $input }}">
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">هاتف</label>
                                    <input type="text" name="mobile" value="{{ old('mobile', $authUser->mobile ?? '') }}" dir="ltr" class="text-start {{ $input }}">
                                </div>
                            </div>
                        </div>

                        <div class="{{ $card }}">
                            <h2 class="font-bold text-24px text-primary mb-8">خيارات الحساب</h2>
                            <div class="divide-y divide-d9">
                                @foreach ([
                                    'newsletter' => 'الاشتراك في النشرة البريدية',
                                    'public_message' => 'استقبال الرسائل العامة',
                                    'enable_profile_statistics' => 'إظهار إحصائيات الملف الشخصي',
                                    'auto_renew_subscription' => 'التجديد التلقائي للاشتراك',
                                ] as $optionKey => $optionLabel)
                                    <div class="flex items-center gap-4 py-5 first:pt-0 last:pb-0">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="{{ $optionKey }}" value="1" class="{{ $toggleClass }}" {{ !empty($authUser->$optionKey) ? 'checked' : '' }}>
                                            <span class="{{ $toggleTrack }}"></span>
                                        </label>
                                        <p class="font-medium text-16px text-black">{{ $optionLabel }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Side column --}}
                    <div class="lg:col-span-5 flex flex-col gap-8">
                        <div class="{{ $card }}">
                            <h2 class="font-bold text-24px text-primary mb-8">تغيير كلمة المرور</h2>
                            <div class="space-y-7">
                                <div class="relative">
                                    <label class="{{ $label }}">كلمة المرور الحالية</label>
                                    <input type="password" name="current_password" value="" autocomplete="current-password" class="{{ $input }}">
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">كلمة المرور الجديدة (اتركها فارغة للإبقاء)</label>
                                    <input type="password" name="password" value="" autocomplete="new-password" class="{{ $input }}">
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">اعد كتابة كلمة المرور الجديدة</label>
                                    <input type="password" name="password_confirmation" value="" autocomplete="new-password" class="{{ $input }}">
                                </div>
                            </div>
                        </div>

                        <div class="{{ $card }}">
                            <h2 class="font-bold text-24px text-primary mb-8">التعريب</h2>
                            <div class="space-y-7">
                                <div class="relative">
                                    <label class="{{ $label }}">اللغة</label>
                                    <select name="language" class="{{ $input }}">
                                        <option value="ar" {{ old('language', $authUser->language ?? 'ar') === 'ar' ? 'selected' : '' }}>العربية</option>
                                        <option value="en" {{ old('language', $authUser->language ?? '') === 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">المنطقة الزمنية</label>
                                    <select name="timezone" class="{{ $input }}">
                                        @foreach (['Asia/Riyadh', 'Asia/Dubai', 'Africa/Cairo', 'Europe/London'] as $timezoneOption)
                                            <option value="{{ $timezoneOption }}" {{ old('timezone', $authUser->timezone ?? 'Asia/Riyadh') === $timezoneOption ? 'selected' : '' }}>{{ $timezoneOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="{{ $card }}">
                            <h2 class="font-bold text-24px text-primary mb-8">وضع الاجازة</h2>
                            <div class="flex items-center gap-4 mb-7">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="offline" value="1" class="{{ $toggleClass }}" {{ !empty($authUser->offline) ? 'checked' : '' }}>
                                    <span class="{{ $toggleTrack }}"></span>
                                </label>
                                <p class="font-medium text-16px text-black">تفعيل وضع الاجازة</p>
                            </div>
                            <div class="relative">
                                <label class="{{ $label }}">رسالة وضع الإجازة</label>
                                <textarea rows="5" name="offline_message"
                                    class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-32 resize-y">{{ old('offline_message', $authUser->offline_message ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    </div>

                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">
                            حفظ الإعدادات
                        </button>
                    </div>
                </form>
            </div>

            <div id="settings-tabs-2" class="hidden" role="tabpanel" aria-labelledby="settings-tabs-item-2">
                <form method="POST" action="{{ route('panel.v1.student.extra.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <div class="lg:col-span-5 {{ $card }}">
                            <h2 class="font-bold text-22px text-primary mb-8">معلومات شخصية</h2>
                            <div class="space-y-6">
                                <div class="relative">
                                    <label class="{{ $label }}">تاريخ الميلاد</label>
                                    <input type="date" name="birthday" value="{{ old('birthday', $birthday) }}"
                                        class="{{ $input }}">
                                </div>
                                <div>
                                    <p class="font-semibold text-15px text-primary mb-3">النوع</p>
                                    <div class="flex gap-6">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gender" value="man" class="radio radio-primary" {{ old('gender', $authUser->gender ?? '') === 'man' ? 'checked' : '' }}><span class="font-medium text-15px">ذكر</span></label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gender" value="woman" class="radio radio-primary" {{ old('gender', $authUser->gender ?? '') === 'woman' ? 'checked' : '' }}><span class="font-medium text-15px">أنثى</span></label>
                                    </div>
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">العنوان</label>
                                    <input type="text" name="address" value="{{ old('address', $authUser->address ?? '') }}"
                                        class="{{ $input }}">
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-7 {{ $card }}">
                            <h2 class="font-bold text-22px text-primary mb-8">المنطقة</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="relative">
                                    <label class="{{ $label }}">الدولة</label>
                                    <select id="js-region-country" name="country_id" class="{{ $input }}">
                                        <option value="">اختر الدولة</option>
                                        @foreach ($countries ?? [] as $country)
                                            <option value="{{ $country->id }}" {{ (string) old('country_id', $authUser->country_id ?? '') === (string) $country->id ? 'selected' : '' }}>{{ $country->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">المنطقة</label>
                                    <select id="js-region-province" name="province_id" data-selected="{{ old('province_id', $authUser->province_id ?? '') }}" class="{{ $input }}">
                                        <option value="">اختر المنطقة</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">المدينة</label>
                                    <select id="js-region-city" name="city_id" data-selected="{{ old('city_id', $authUser->city_id ?? '') }}" class="{{ $input }}">
                                        <option value="">اختر المدينة</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">الحي</label>
                                    <select id="js-region-district" name="district_id" data-selected="{{ old('district_id', $authUser->district_id ?? '') }}" class="{{ $input }}">
                                        <option value="">اختر الحي</option>
                                    </select>
                                </div>
                            </div>
                            @if (!empty($formFieldsHtml))
                                <div class="mt-8 border-t border-d9 pt-8">
                                    <h3 class="font-bold text-18px text-primary mb-4">حقول إضافية</h3>
                                    {!! $formFieldsHtml !!}
                                </div>
                            @endif
                            <div class="mt-8">
                                <h3 class="font-bold text-18px text-primary mb-4">التواصل الاجتماعي</h3>
                                <div class="space-y-4">
                                    @foreach ($socials ?? [] as $socialKey => $socialValue)
                                        @if (!empty($socialValue['title']))
                                            <div class="relative">
                                                <label class="{{ $label }}">{{ $socialValue['title'] }}</label>
                                                <input type="text" name="socials[{{ $socialKey }}]" value="{{ old('socials.' . $socialKey, $userSocials[$socialKey] ?? '') }}" dir="ltr" class="text-start {{ $input }}">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">حفظ المعلومات الإضافية</button>
                    </div>
                </form>
            </div>

            <div id="settings-tabs-3" class="hidden" role="tabpanel" aria-labelledby="settings-tabs-item-3">
                <form method="POST" action="{{ route('panel.v1.student.financial.update') }}" enctype="multipart/form-data">
                    @csrf
                    @if (!empty($authUser->financial_approval))
                        <div class="rounded-12px bg-[#ECFDF5] border border-[#A7F3D0]/60 px-6 py-5 mb-6">
                            <p class="font-bold text-16px text-primary">تم توثيق الهوية والبيانات المالية</p>
                        </div>
                    @else
                        <div class="rounded-12px bg-[#FEF6E7] border border-[#F5D9A8]/60 px-6 py-5 mb-6">
                            <p class="font-bold text-16px text-primary">بانتظار توثيق الهوية والبيانات المالية من الإدارة</p>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <div class="lg:col-span-5 {{ $card }}">
                            <h2 class="font-bold text-22px text-primary mb-8">حساب السحب</h2>
                            <div class="relative">
                                <label class="{{ $label }}">نوع الحساب البنكي</label>
                                <select name="bank_id" id="js-user-bank-input" class="{{ $input }}" {{ !empty($authUser->financial_approval) ? 'disabled' : '' }}>
                                    <option value="">اختر الحساب</option>
                                    @foreach ($userBanks ?? [] as $bank)
                                        <option value="{{ $bank->id }}" data-specifications='@json($bank->specifications->pluck('name', 'id'))' {{ (int) old('bank_id', optional($authUser->selectedBank)->user_bank_id ?? 0) === (int) $bank->id ? 'selected' : '' }}>{{ $bank->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="js-bank-specifications-card mt-6 space-y-6">
                                @if (!empty($authUser->selectedBank) && !empty($authUser->selectedBank->bank))
                                    @foreach ($authUser->selectedBank->bank->specifications as $bankSpecification)
                                        @php
                                            $selectedSpecification = $authUser->selectedBank->specifications->firstWhere('user_bank_specification_id', $bankSpecification->id);
                                        @endphp
                                        <div class="relative">
                                            <label class="{{ $label }}">{{ $bankSpecification->name }}</label>
                                            <input type="text" name="bank_specifications[{{ $bankSpecification->id }}]" value="{{ $selectedSpecification->value ?? '' }}"
                                                class="{{ $input }}" {{ !empty($authUser->financial_approval) ? 'disabled' : '' }}>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="lg:col-span-7 {{ $card }}">
                            <h2 class="font-bold text-22px text-primary mb-8">وثائق الهوية</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @foreach ([['identity_scan', 'صورة الهوية'], ['certificate', 'الشهادة والمستندات']] as [$field, $label])
                                    <div>
                                        <label class="font-semibold text-15px text-primary mb-3 block">{{ $label }}</label>
                                        <label class="flex flex-col items-center justify-center gap-2 min-h-40 rounded-12px border border-dashed border-d9 bg-[#F7F0E6]/40 px-4 py-8 cursor-pointer hover:border-primary/40 transition">
                                            <span class="font-semibold text-14px text-primary">اختر ملفًا</span>
                                            <input type="file" name="{{ $field }}" class="hidden" {{ !empty($authUser->financial_approval) ? 'disabled' : '' }}>
                                        </label>
                                        @if (!empty($authUser->$field))
                                            <p class="font-medium text-13px text-[#00B31B] mt-2">يوجد ملف مرفوع</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if (empty($authUser->financial_approval))
                        <div class="mt-8">
                            <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">حفظ البيانات المالية</button>
                        </div>
                    @endif
                </form>
            </div>

            <div id="settings-tabs-4" class="hidden" role="tabpanel" aria-labelledby="settings-tabs-item-4">
                <form method="POST" action="{{ route('panel.v1.student.images.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ([['avatar', 'الصورة الشخصية'], ['cover_img', 'صورة الغلاف'], ['profile_secondary_image', 'الصورة الثانوية'], ['profile_video', 'فيديو الملف']] as [$field, $label])
                            <div class="{{ $card }}">
                                <h2 class="font-bold text-20px text-primary mb-6">{{ $label }}</h2>
                                @if ($field === 'avatar')
                                    <div class="flex items-center gap-4 mb-6"><img src="{{ $authUser->getAvatar(80) }}" alt="" class="size-20 rounded-full object-cover"></div>
                                @endif
                                <label class="flex items-center justify-center gap-2 min-h-40 rounded-12px border border-dashed border-d9 cursor-pointer hover:border-primary/40 transition px-4 py-8">
                                    <span class="font-semibold text-14px text-primary">اختر ملفًا</span>
                                    <input type="file" name="{{ $field }}" class="hidden" accept="{{ $field === 'profile_video' ? 'video/*' : 'image/*' }}">
                                </label>
                                @if (!empty($authUser->$field))
                                    <form method="POST" action="{{ route('panel.v1.student.media.delete', ['type' => $field]) }}" onsubmit="return confirm('حذف الملف؟');" class="mt-4">
                                        @csrf
                                        <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف الملف الحالي</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                        <div class="{{ $card }}">
                            <h2 class="font-bold text-20px text-primary mb-6">التوقيع</h2>
                            @php $signature = $authUser->getSignature(true); @endphp
                            @if (!empty($signature))
                                <div class="mb-6"><img src="{{ $signature }}" alt="" class="max-h-32 rounded-10px"></div>
                                <form method="POST" action="{{ route('panel.v1.student.media.delete', ['type' => 'signature_img']) }}" onsubmit="return confirm('حذف التوقيع؟');" class="mb-4">
                                    @csrf
                                    <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف التوقيع</button>
                                </form>
                            @endif
                            <label class="flex items-center justify-center gap-2 min-h-40 rounded-12px border border-dashed border-d9 cursor-pointer hover:border-primary/40 transition px-4 py-8">
                                <span class="font-semibold text-14px text-primary">{{ !empty($signature) ? 'استبدال التوقيع' : 'رفع التوقيع' }}</span>
                                <input type="file" name="signature_img" class="hidden" accept="image/*">
                            </label>
                        </div>
                    </div>
                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">حفظ الصور</button>
                    </div>
                </form>
            </div>

            <div id="settings-tabs-5" class="hidden" role="tabpanel" aria-labelledby="settings-tabs-item-5">
                <form method="POST" action="{{ route('panel.v1.student.about.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <div class="lg:col-span-7 {{ $card }}">
                            <h2 class="font-bold text-22px text-primary mb-8">نبذة وتعريف</h2>
                            <div class="space-y-6">
                                <div class="relative">
                                    <label class="{{ $label }}">المسمى الوظيفي</label>
                                    <input type="text" name="headline" value="{{ old('headline', $authUser->headline ?? '') }}"
                                        class="{{ $input }}">
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">نبذة قصيرة</label>
                                    <textarea rows="3" name="bio" class="textarea textarea-bordered w-full rounded-10px font-medium text-16px focus:outline-none focus:border-primary">{{ old('bio', $authUser->bio ?? '') }}</textarea>
                                </div>
                                <div class="relative">
                                    <label class="{{ $label }}">من أنا (تفصيلي)</label>
                                    <textarea rows="6" name="about" class="textarea textarea-bordered w-full rounded-10px font-medium text-16px focus:outline-none focus:border-primary">{{ old('about', $authUser->about ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-5 flex flex-col gap-6">
                            <div class="{{ $card }}">
                                <h2 class="font-bold text-22px text-primary mb-6">المؤهلات العلمية</h2>
                                <div class="space-y-3 mb-6">
                                    @forelse ($educations ?? [] as $education)
                                        <div class="flex items-center justify-between gap-3 border border-d9 rounded-10px px-4 py-3">
                                            <span class="font-medium text-15px">{{ $education->value }}</span>
                                            <form method="POST" action="{{ route('panel.v1.student.metas.delete', ['metaId' => $education->id]) }}" onsubmit="return confirm('حذف؟');">
                                                @csrf
                                                <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف</button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="font-medium text-14px text-gray">لا توجد مؤهلات مسجلة.</p>
                                    @endforelse
                                </div>
                                <div class="flex gap-3">
                                    <input type="text" id="student-education-val" placeholder="مؤهل جديد" class="input input-bordered flex-1 h-12 rounded-10px font-medium text-15px focus:outline-none focus:border-primary">
                                    <button type="button" id="student-education-add" class="btn btn-primary rounded-10px h-12 px-6 font-bold text-15px shrink-0">إضافة</button>
                                </div>
                            </div>
                            <div class="{{ $card }}">
                                <h2 class="font-bold text-22px text-primary mb-6">الخبرات العملية</h2>
                                <div class="space-y-3 mb-6">
                                    @forelse ($experiences ?? [] as $experience)
                                        <div class="flex items-center justify-between gap-3 border border-d9 rounded-10px px-4 py-3">
                                            <span class="font-medium text-15px">{{ $experience->value }}</span>
                                            <form method="POST" action="{{ route('panel.v1.student.metas.delete', ['metaId' => $experience->id]) }}" onsubmit="return confirm('حذف؟');">
                                                @csrf
                                                <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف</button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="font-medium text-14px text-gray">لا توجد خبرات مسجلة.</p>
                                    @endforelse
                                </div>
                                <div class="flex gap-3">
                                    <input type="text" id="student-experience-val" placeholder="خبرة جديدة" class="input input-bordered flex-1 h-12 rounded-10px font-medium text-15px focus:outline-none focus:border-primary">
                                    <button type="button" id="student-experience-add" class="btn btn-primary rounded-10px h-12 px-6 font-bold text-15px shrink-0">إضافة</button>
                                </div>
                            </div>
                            <div class="{{ $card }}">
                                <h2 class="font-bold text-22px text-primary mb-6">الملفات والمرفقات</h2>
                                <div class="space-y-3 mb-6">
                                    @forelse ($attachments ?? [] as $attachment)
                                        <div class="flex items-center justify-between gap-3 border border-d9 rounded-10px px-4 py-3">
                                            <div class="min-w-0">
                                                <span class="block font-medium text-15px truncate">{{ $attachment->title }}</span>
                                                @if (!empty($attachment->description))
                                                    <span class="block font-medium text-12px text-gray truncate">{{ $attachment->description }}</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-3 shrink-0">
                                                @if (!empty($attachment->attachment))
                                                    <a href="{{ $attachment->attachment }}" target="_blank" rel="noopener" class="font-bold text-14px text-primary">تنزيل</a>
                                                @endif
                                                <form method="POST" action="{{ route('panel.v1.student.attachments.delete', ['attachmentId' => $attachment->id]) }}" onsubmit="return confirm('حذف؟');">
                                                    @csrf
                                                    <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="font-medium text-14px text-gray">لا توجد مرفقات.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">حفظ بيانات حول</button>
                    </div>
                </form>
                <div class="{{ $card }} mt-6">
                    <h2 class="font-bold text-22px text-primary mb-6">رفع مرفق جديد</h2>
                    <form method="POST" action="{{ route('panel.v1.student.attachments.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <input type="text" name="title" required placeholder="عنوان المرفق" class="input input-bordered h-12 rounded-10px font-medium text-15px focus:outline-none focus:border-primary">
                        <select name="file_type" class="select select-bordered h-12 rounded-10px font-medium text-15px focus:outline-none focus:border-primary">
                            @foreach (['document' => 'مستند', 'image' => 'صورة', 'video' => 'فيديو', 'pdf' => 'PDF', 'archive' => 'مضغوط'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="description" placeholder="وصف مختصر (اختياري)" class="input input-bordered h-12 rounded-10px font-medium text-15px focus:outline-none focus:border-primary sm:col-span-2">
                        <input type="file" name="attachment" required class="input input-bordered h-12 rounded-10px font-medium text-15px focus:outline-none focus:border-primary sm:col-span-2">
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px">رفع المرفق</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="settings-tabs-6" class="hidden" role="tabpanel" aria-labelledby="settings-tabs-item-6">
                <div class="{{ $card }}">
                    <h2 class="font-bold text-22px text-primary mb-6">سجل الدخول</h2>
                    <div class="overflow-x-auto">
                        <table class="table w-full text-15px">
                            <thead>
                                <tr class="border-b border-d9 text-gray">
                                    <th class="px-4 py-3 text-start font-semibold">المتصفح</th>
                                    <th class="px-4 py-3 text-start font-semibold">الجهاز</th>
                                    <th class="px-4 py-3 text-start font-semibold">نظام التشغيل</th>
                                    <th class="px-4 py-3 text-start font-semibold">IP</th>
                                    <th class="px-4 py-3 text-start font-semibold">البداية</th>
                                    <th class="px-4 py-3 text-start font-semibold">النهاية</th>
                                    <th class="px-4 py-3 text-start font-semibold">المدة</th>
                                    <th class="px-4 py-3 text-start font-semibold">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loginHistories ?? [] as $session)
                                    <tr class="border-b border-d9 last:border-0">
                                        <td class="px-4 py-4">{{ $session->browser ?? '-' }}</td>
                                        <td class="px-4 py-4">{{ $session->device ?? '-' }}</td>
                                        <td class="px-4 py-4">{{ $session->os ?? '-' }}</td>
                                        <td class="px-4 py-4" dir="ltr">{{ $session->ip ?? '-' }}</td>
                                        <td class="px-4 py-4">{{ !empty($session->session_start_at) ? date('Y/m/d H:i', (int) $session->session_start_at) : '-' }}</td>
                                        <td class="px-4 py-4">{{ !empty($session->session_end_at) ? date('Y/m/d H:i', (int) $session->session_end_at) : 'نشطة' }}</td>
                                        <td class="px-4 py-4">{{ $session->getDuration() }}</td>
                                        <td class="px-4 py-4">
                                            @if (empty($session->session_end_at))
                                                <form method="POST" action="{{ route('panel.v1.student.sessions.end', ['sessionId' => $session->id]) }}" onsubmit="return confirm('إنهاء هذه الجلسة؟');">
                                                    @csrf
                                                    <button type="submit" class="font-bold text-14px text-[#EF4444]">إنهاء الجلسة</button>
                                                </form>
                                            @else
                                                <span class="text-gray">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center font-medium text-16px text-gray">لا يوجد سجل دخول بعد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Quick add for education/experience metas
    function postMeta(name, inputId) {
        var input = document.getElementById(inputId);
        if (!input || !input.value.trim()) return;
        fetch("{{ route('panel.v1.student.metas.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({name: name, value: input.value.trim()}),
        }).then(function (r) { if (r.ok) window.location.reload(); });
    }
    document.getElementById('student-education-add')?.addEventListener('click', function () { postMeta('education', 'student-education-val'); });
    document.getElementById('student-experience-add')?.addEventListener('click', function () { postMeta('experience', 'student-experience-val'); });

    // Chained region selects (GET /regions/* endpoints)
    var regionCountry = document.getElementById('js-region-country');
    if (regionCountry) {
        var regionProvince = document.getElementById('js-region-province');
        var regionCity = document.getElementById('js-region-city');
        var regionDistrict = document.getElementById('js-region-district');

        function fetchRegion(url, key, select, placeholder, done) {
            if (!select) { if (done) done(); return; }
            select.innerHTML = '<option value="">' + placeholder + '</option>';
            if (!url) { if (done) done(); return; }
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    (data[key] || []).forEach(function (item) {
                        var option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.title;
                        if (select.dataset.selected && String(item.id) === String(select.dataset.selected)) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                    if (done) done();
                })
                .catch(function () { if (done) done(); });
        }
        function loadProvinces() {
            fetchRegion(regionCountry.value ? '/regions/provincesByCountry/' + regionCountry.value : null,
                'provinces', regionProvince, 'اختر المنطقة', loadCities);
        }
        function loadCities() {
            fetchRegion(regionProvince && regionProvince.value ? '/regions/citiesByProvince/' + regionProvince.value : null,
                'cities', regionCity, 'اختر المدينة', loadDistricts);
        }
        function loadDistricts() {
            fetchRegion(regionCity && regionCity.value ? '/regions/districtsByCity/' + regionCity.value : null,
                'districts', regionDistrict, 'اختر الحي');
        }

        regionCountry.addEventListener('change', function () {
            [regionProvince, regionCity, regionDistrict].forEach(function (sel) { if (sel) sel.dataset.selected = ''; });
            loadProvinces();
        });
        if (regionProvince) regionProvince.addEventListener('change', function () {
            [regionCity, regionDistrict].forEach(function (sel) { if (sel) sel.dataset.selected = ''; });
            loadCities();
        });
        if (regionCity) regionCity.addEventListener('change', function () {
            if (regionDistrict) regionDistrict.dataset.selected = '';
            loadDistricts();
        });

        if (regionCountry.value) loadProvinces();
    }

    // Bank specifications rendered from data-specifications of the selected option
    var bankInput = document.getElementById('js-user-bank-input');
    if (bankInput) {
        var specsCard = document.querySelector('.js-bank-specifications-card');
        bankInput.addEventListener('change', function () {
            if (!specsCard) return;
            var option = bankInput.options[bankInput.selectedIndex];
            var specs = option ? option.getAttribute('data-specifications') : null;
            var html = '';
            if (specs) {
                Object.entries(JSON.parse(specs)).forEach(function (entry) {
                    html += '<div class="relative">' +
                        '<label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">' + entry[1] + '</label>' +
                        '<input type="text" name="bank_specifications[' + entry[0] + ']" class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary">' +
                        '</div>';
                });
            }
            specsCard.innerHTML = html;
        });
    }
});
</script>
@endpush
@endsection
