@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $name = $authUser->full_name ?? ($profileName ?? $instructorName ?? '');
    $email = $authUser->email ?? ($profileEmail ?? $instructorEmail ?? '');
    $phone = $authUser->mobile ?? ($profilePhone ?? '');
    $input = 'input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary';
    $select = 'select select-bordered w-full h-14 min-h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary';
    $textarea = 'textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-32 resize-y';
    $label = 'absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $uploadBtn = 'flex items-center justify-center gap-2 w-full py-4 rounded-10px border border-dashed border-d9 font-semibold text-15px text-primary hover:bg-primary/5 transition cursor-pointer';
    $mediaBox = 'flex items-center justify-center w-full min-h-40 rounded-12px bg-[#F5F5F5] border border-d9';
@endphp

<div class="space-y-8 sm:space-y-10 pb-10">
    <div class="min-w-0">
        <h1 class="font-semibold text-28px sm:text-32px text-primary mb-2">إعدادات الملف الشخصي</h1>
        <p class="font-medium text-16px sm:text-18px text-gray">إدارة إعدادات حسابك</p>
    </div>

    <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-2 border-b border-d9"
        aria-label="أقسام إعدادات الحساب" role="tablist">
        @foreach ([
            1 => 'معلومات أساسية',
            2 => 'معلومات إضافية',
            3 => 'الهوية والمالية',
            4 => 'الصور',
            5 => 'حول',
            6 => 'سجل الدخول',
        ] as $tabId => $tabLabel)
            <button type="button"
                class="tab {{ $tabId === 1 ? 'active' : '' }} justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                id="instructor-settings-tab-{{ $tabId }}" data-tab="#instructor-settings-{{ $tabId }}" role="tab"
                aria-selected="{{ $tabId === 1 ? 'true' : 'false' }}">
                {{ $tabLabel }}
            </button>
        @endforeach
    </nav>

    {{-- ===================== Tab 1: Basic ===================== --}}
    <div id="instructor-settings-1" role="tabpanel" aria-labelledby="instructor-settings-tab-1">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6 items-start">
            {{-- Account & security + delete --}}
            <div class="flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">الحساب والأمان</h2>
                    <div class="space-y-6">
                        <div class="relative">
                            <label class="{{ $label }}">الاسم</label>
                            <input type="text" value="{{ $name }}" class="{{ $input }}">
                        </div>
                        <div class="relative">
                            <label class="{{ $label }}">البريد الإلكتروني</label>
                            <input type="email" value="{{ $email }}" class="{{ $input }}">
                        </div>
                        <div class="relative">
                            <label class="{{ $label }}">هاتف</label>
                            <div class="flex gap-2">
                                <select class="{{ $select }} !w-[42%] shrink-0" aria-label="رمز الدولة">
                                    <option value="+966" selected>السعودية (+966)</option>
                                    <option value="+971">الإمارات (+971)</option>
                                    <option value="+20">مصر (+20)</option>
                                    <option value="+962">الأردن (+962)</option>
                                </select>
                                <input type="tel" value="{{ $phone }}" class="{{ $input }} flex-1" placeholder="5xxxxxxxx">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="{{ $label }}">كلمة المرور</label>
                            <input type="password" class="{{ $input }}">
                        </div>
                        <div class="relative">
                            <label class="{{ $label }}">أعد كتابة كلمة المرور</label>
                            <input type="password" class="{{ $input }}">
                        </div>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-4 text-start">حذف الحساب</h2>
                    <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed mb-5 text-start">
                        يمكنك طلب حذف معلومات حسابك. سنراجع طلبك، وسيتم إزالة جميع بياناتك نهائيًا.
                    </p>
                    <button type="button"
                        class="w-full h-12 rounded-10px border border-red-500 text-red-500 font-semibold text-16px hover:bg-red-50 transition">
                        حذف الحساب
                    </button>
                </div>
            </div>

            {{-- Localization + vacation --}}
            <div class="flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">التعريب</h2>
                    <div class="space-y-6">
                        <div class="relative">
                            <label class="{{ $label }}">لغة</label>
                            <select class="{{ $select }}">
                                <option selected>العربية</option>
                                <option>English</option>
                            </select>
                        </div>
                        <div class="relative">
                            <label class="{{ $label }}">المنطقة الزمنية</label>
                            <select class="{{ $select }}">
                                <option value="">اختر</option>
                                <option selected>Asia/Riyadh</option>
                                <option>Asia/Dubai</option>
                                <option>Africa/Cairo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">وضع الإجازة</h2>
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <p class="font-medium text-15px sm:text-16px text-primary text-start">تفعيل وضع الإجازة</p>
                        <input type="checkbox" class="switch switch-primary shrink-0" aria-label="تفعيل وضع الإجازة">
                    </div>
                    <div class="relative mb-4">
                        <label class="{{ $label }}">رسالة أوفلاين</label>
                        <textarea rows="5" class="{{ $textarea }}"></textarea>
                    </div>
                    <p class="font-medium text-13px sm:text-14px text-gray leading-relaxed text-start">
                        عند عدم نشاط حسابك، ستظهر رسالة في ملفك الشخصي. يمكنك إضافة رسالة شخصية أدناه.
                    </p>
                </div>
            </div>

            {{-- Account options --}}
            <div class="{{ $card }}">
                <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 text-start">خيارات الحساب</h2>
                <div class="center mb-6 sm:mb-8">
                    <img src="/assets/design_1/img/panel/settings/account_options.svg" alt=""
                        class="w-full max-w-[220px] h-auto" width="220" height="190">
                </div>
                <div class="divide-y divide-d9">
                    @foreach ($accountOptions ?? [] as $opt)
                        <div class="flex items-center justify-between gap-4 py-5 first:pt-0 last:pb-0">
                            <p class="font-medium text-14px sm:text-15px text-primary text-start leading-snug">{{ $opt }}</p>
                            <input type="checkbox" class="switch switch-primary shrink-0" aria-label="{{ $opt }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Tab 2: Extra ===================== --}}
    <div id="instructor-settings-2" class="hidden" role="tabpanel" aria-labelledby="instructor-settings-tab-2">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
            <div class="lg:col-span-4 flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">المعلومات الشخصية</h2>
                    <div class="space-y-6">
                        <div class="relative">
                            <label class="{{ $label }}">تاريخ الميلاد</label>
                            <input type="date" class="{{ $input }}">
                        </div>
                        <div>
                            <p class="font-semibold text-15px text-primary mb-4 text-start">الجنس</p>
                            <div class="flex flex-wrap gap-5">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="gender" value="man" class="radio radio-primary" checked>
                                    <span class="font-medium text-15px text-primary">ذكر</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="gender" value="woman" class="radio radio-primary">
                                    <span class="font-medium text-15px text-primary">أنثى</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">إعدادات الاجتماع</h2>
                    <div class="mb-7">
                        <p class="font-semibold text-15px text-primary mb-4 text-start">نوع الاجتماع</p>
                        <div class="space-y-3.5">
                            @foreach ([['in_person', 'وجهاً لوجه'], ['online', 'عبر الإنترنت'], ['all', 'الجميع']] as [$val, $txt])
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="meeting_type" value="{{ $val }}"
                                        class="radio radio-primary" {{ $val === 'all' ? 'checked' : '' }}>
                                    <span class="font-medium text-15px text-primary">{{ $txt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-15px text-primary mb-4 text-start">مستوى التدريب</p>
                        <div class="space-y-3.5">
                            @foreach ([['beginner', 'مبتدئ'], ['middle', 'متوسط'], ['expert', 'متقدم']] as [$val, $txt])
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="level_of_training[]" value="{{ $val }}"
                                        class="checkbox checkbox-primary">
                                    <span class="font-medium text-15px text-primary">{{ $txt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 sm:gap-6">
                        <div class="md:col-span-5 space-y-5">
                            <h2 class="font-bold text-20px sm:text-22px text-primary mb-2 text-start">المنطقة</h2>
                            @foreach ([
                                ['الدولة', 'اختر دولة'],
                                ['الولاية', 'اختر ولاية'],
                                ['المدينة', 'اختر مدينة'],
                                ['الحي', 'جميع المناطق'],
                            ] as [$lbl, $ph])
                                <div class="relative">
                                    <label class="{{ $label }}">{{ $lbl }}</label>
                                    <select class="{{ $select }}">
                                        <option value="">{{ $ph }}</option>
                                    </select>
                                </div>
                            @endforeach
                            <div class="relative">
                                <label class="{{ $label }}">العنوان</label>
                                <input type="text" class="{{ $input }}">
                            </div>
                        </div>
                        <div class="md:col-span-7">
                            <h2 class="font-bold text-20px sm:text-22px text-primary mb-4 text-start">الموقع على الخريطة</h2>
                            <div class="relative w-full h-72 sm:h-80 rounded-12px overflow-hidden border border-d9 bg-[#E8EEF2]">
                                <div class="absolute inset-0 opacity-40"
                                    style="background-image: radial-gradient(circle at 20% 30%, #c5d4dc 1px, transparent 1px), radial-gradient(circle at 80% 70%, #c5d4dc 1px, transparent 1px); background-size: 24px 24px;"></div>
                                <div class="absolute inset-0 center">
                                    <span class="size-10 rounded-full bg-primary/15 center">
                                        <svg class="size-5 text-primary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">الشبكات الاجتماعية</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach ($socialNetworks ?? [] as $social)
                            <div class="flex items-center gap-3">
                                <span class="size-12 shrink-0 rounded-12px bg-[#F5F5F5] border border-d9 center font-bold text-13px text-primary uppercase">
                                    {{ mb_substr($social['label'], 0, 1) }}
                                </span>
                                <div class="relative flex-1 min-w-0">
                                    <label class="{{ $label }}">{{ $social['label'] }}</label>
                                    <input type="url" class="{{ $input }}" placeholder="https://">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-start gap-3 mt-6 pt-5 border-t border-d9">
                        <span class="size-10 shrink-0 rounded-10px bg-[#F5F5F5] center text-gray font-bold text-16px">i</span>
                        <div class="min-w-0 text-start">
                            <p class="font-semibold text-15px text-primary mb-1">ملاحظة</p>
                            <p class="font-medium text-13px sm:text-14px text-gray leading-relaxed">
                                اترك روابط الشبكات الاجتماعية فارغة لإخفائها من ملفك العام.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Tab 3: Identity & financial ===================== --}}
    <div id="instructor-settings-3" class="hidden" role="tabpanel" aria-labelledby="instructor-settings-tab-3">
        <div class="space-y-5 sm:space-y-6">
            @if (empty($identityVerified))
                <div class="flex items-start gap-4 rounded-14px border border-[#C99C69]/40 bg-[#C99C69]/15 px-5 py-4 sm:px-6 sm:py-5">
                    <span class="size-12 shrink-0 rounded-12px bg-[#C99C69] center text-white font-bold text-20px">⋯</span>
                    <div class="min-w-0 text-start">
                        <p class="font-bold text-16px sm:text-18px text-primary mb-1">الموافقة على الهوية</p>
                        <p class="font-medium text-14px sm:text-15px text-primary/80 leading-relaxed">
                            لم يتم التحقق من هويتك يمكن أن يسبب لك ذلك تأخراً في دفع مستحقاتك. لتفادي ذلك يرجى تأكيدها عن طريق ملء الحقول التالية.
                        </p>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-4 rounded-14px border border-primary/20 bg-primary/10 px-5 py-4 sm:px-6 sm:py-5">
                    <span class="size-12 shrink-0 rounded-12px bg-primary center text-white font-bold text-18px">✓</span>
                    <div class="min-w-0 text-start">
                        <p class="font-bold text-16px sm:text-18px text-primary mb-1">تم التحقق من هويتك</p>
                        <p class="font-medium text-14px sm:text-15px text-primary/80 leading-relaxed">
                            تم التحقق من الهوية والمعلومات المالية بنجاح.
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
                <div class="lg:col-span-4 {{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">حساب الدفع</h2>
                    <div class="relative">
                        <label class="{{ $label }}">حدد نوع الحساب</label>
                        <select class="{{ $select }}">
                            <option value="">حدد نوع الحساب</option>
                            <option selected>{{ $paymentAccount ?? 'ماي فاتورة' }}</option>
                            <option>تحويل بنكي</option>
                            <option>PayPal</option>
                        </select>
                    </div>
                </div>

                <div class="lg:col-span-8 {{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 sm:mb-8 text-start">وثائق الهوية</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        @foreach (['مسح الهوية', 'الشهادات والوثائق'] as $uploadLabel)
                            <label class="flex flex-col items-center justify-center gap-3 min-h-44 rounded-14px border border-dashed border-d9 bg-[#FAFAFA] cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition px-4 py-8">
                                <span class="size-12 rounded-full bg-primary/10 center">
                                    <svg class="size-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </span>
                                <span class="font-semibold text-15px text-primary">{{ $uploadLabel }}</span>
                                <input type="file" class="hidden" accept="image/*,.pdf">
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Tab 4: Images ===================== --}}
    <div id="instructor-settings-4" class="hidden" role="tabpanel" aria-labelledby="instructor-settings-tab-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-5 sm:gap-6 items-stretch">
            <div class="xl:col-span-4 flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 text-start">صورة الملف الشخصي</h2>
                    <div class="center flex-col mb-5">
                        <div class="relative size-24 rounded-full bg-primary/10 center overflow-hidden mb-3">
                            <span class="font-bold text-28px text-primary">{{ mb_substr($name, 0, 1) }}</span>
                        </div>
                        <p class="font-semibold text-16px text-primary text-center">{{ $name }}</p>
                    </div>
                    <label class="{{ $uploadBtn }}">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        حدد صورة
                        <input type="file" class="hidden" accept="image/*">
                    </label>
                </div>

                <div class="{{ $card }} flex-1">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-5 text-start">غلاف الملف الشخصي</h2>
                    <div class="{{ $mediaBox }} mb-4 min-h-36">
                        <span class="size-12 rounded-12px bg-primary/10 center">
                            <svg class="size-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                    <p class="font-medium text-13px sm:text-14px text-gray leading-relaxed mb-4 text-start">
                        ستُستخدم هذه الصورة كخلفية ضبابية لصفحة ملفك الشخصي. الطول الموصى به: 320 بكسل.
                    </p>
                    <label class="{{ $uploadBtn }}">
                        حدد صورة
                        <input type="file" class="hidden" accept="image/*">
                    </label>
                </div>
            </div>

            <div class="xl:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-5 sm:gap-6">
                @foreach ([
                    [
                        'title' => 'صورة الملف الثانوي',
                        'hint' => 'ستظهر هذه الصورة في صفحات الدورات والمنتجات الخاصة بك. للحصول على أفضل تجربة، استخدم ملف PNG شفاف.',
                        'action' => 'حدد صورة',
                        'accept' => 'image/*',
                        'icon' => 'image',
                    ],
                    [
                        'title' => 'فيديو الملف الشخصي',
                        'hint' => 'سيتم عرض هذا الفيديو في صفحة ملفك الشخصي. نوصي بأن تكون مدته بين 2 إلى 4 دقائق.',
                        'action' => 'اختر فيديو',
                        'accept' => 'video/*',
                        'icon' => 'video',
                    ],
                    [
                        'title' => 'التوقيع',
                        'hint' => 'ستظهر التوقيع على شهاداتك. يرجى رفع ملف PNG أو JPG بخلفية بيضاء.',
                        'action' => 'حدد صورة',
                        'accept' => 'image/*',
                        'icon' => 'image',
                    ],
                ] as $media)
                    <div class="{{ $card }} flex flex-col h-full">
                        <h2 class="font-bold text-18px sm:text-20px text-primary mb-4 text-start">{{ $media['title'] }}</h2>
                        <div class="{{ $mediaBox }} mb-4 aspect-square max-h-52">
                            @if ($media['icon'] === 'video')
                                <span class="size-12 rounded-full bg-primary/10 center">
                                    <svg class="size-6 text-primary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </span>
                            @else
                                <span class="size-12 rounded-12px bg-primary/10 center">
                                    <svg class="size-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <p class="font-medium text-13px sm:text-14px text-gray leading-relaxed mb-4 text-start flex-1">{{ $media['hint'] }}</p>
                        <label class="{{ $uploadBtn }} mt-auto">
                            {{ $media['action'] }}
                            <input type="file" class="hidden" accept="{{ $media['accept'] }}">
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== Tab 5: About ===================== --}}
    <div id="instructor-settings-5" class="hidden" role="tabpanel" aria-labelledby="instructor-settings-tab-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6 items-start">
            <div class="flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-6 p-4 rounded-12px border border-dashed border-d9">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="size-12 shrink-0 rounded-12px bg-primary/10 center">
                                <svg class="size-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 16.5c0 1.657-4.03 3-9 3s-9-1.343-9-3c0-1.41 1.47-2.643 3.84-3.422L12 14z"/>
                                </svg>
                            </span>
                            <div class="min-w-0 text-start">
                                <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">السجل التعليمي</h2>
                                <p class="font-medium text-13px text-gray">أضف درجاتك العلمية لتظهر في ملفك الشخصي.</p>
                            </div>
                        </div>
                        <button type="button" class="font-semibold text-15px text-primary hover:opacity-80 shrink-0">+ إضافة تعليم</button>
                    </div>
                    <div class="center flex-col py-14 text-center">
                        <span class="size-16 rounded-12px bg-primary/10 center mb-4">
                            <svg class="size-8 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 16.5c0 1.657-4.03 3-9 3s-9-1.343-9-3c0-1.41 1.47-2.643 3.84-3.422L12 14z"/>
                            </svg>
                        </span>
                        <p class="font-semibold text-17px text-primary mb-2">لم تتم إضافة أي درجة.</p>
                        <p class="font-medium text-14px text-gray">سيتم عرض مؤهلاتك الأكاديمية في ملفك الشخصي.</p>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 text-start">حول</h2>
                    <div class="space-y-6">
                        <div class="relative">
                            <label class="{{ $label }}">الوظيفة</label>
                            <input type="text" value="{{ $profileJobTitle ?? '' }}" class="{{ $input }}">
                        </div>
                        <div class="rounded-12px bg-[#F5F5F5] border border-d9 px-4 py-3 text-start">
                            <p class="font-medium text-13px text-gray mb-1">- المسار الوظيفي سيتم عرضه أسفل اسمك وعلى الملف الشخصي الخاص بك.</p>
                            <p class="font-medium text-13px text-gray">- اجعله قصيراً من 2 إلى 3 أسطر.</p>
                        </div>
                        <div class="relative">
                            <label class="{{ $label }}">السيرة الذاتية</label>
                            <textarea rows="8" class="{{ $textarea }}">{{ $profileBio ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5 sm:gap-6">
                <div class="{{ $card }}">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-6 p-4 rounded-12px border border-dashed border-d9">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="size-12 shrink-0 rounded-12px bg-primary/10 center">
                                <svg class="size-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <div class="min-w-0 text-start">
                                <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">السجل المهني</h2>
                                <p class="font-medium text-13px text-gray">أضف خبراتك العملية لتظهر في ملفك الشخصي.</p>
                            </div>
                        </div>
                        <button type="button" class="font-semibold text-15px text-primary hover:opacity-80 shrink-0">+ إضافة خبرات</button>
                    </div>
                    <div class="center flex-col py-14 text-center">
                        <span class="size-16 rounded-12px bg-primary/10 center mb-4">
                            <svg class="size-8 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <p class="font-semibold text-17px text-primary mb-2">لم تتم إضافة أي خبرة.</p>
                        <p class="font-medium text-14px text-gray">أضف خبرتك المهنية لمساعدة المستخدمين على معرفة المزيد عنك.</p>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <h2 class="font-bold text-20px sm:text-22px text-primary mb-6 text-start">المهارات</h2>
                    <div class="relative mb-4">
                        <label class="{{ $label }}">اختر مهاراتك</label>
                        <select class="{{ $select }}" multiple size="4">
                            @foreach ($skillOptions ?? [] as $skill)
                                <option>{{ $skill }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rounded-12px bg-[#F5F5F5] border border-d9 px-4 py-3 text-start">
                        <p class="font-medium text-13px text-gray mb-1">- اختر اهتماماتك.</p>
                        <p class="font-medium text-13px text-gray">- إذا كنت تقوم بفصول مباشرة، فاختر موضوعات أكثر دقة مقترحة.</p>
                    </div>
                </div>

                <div class="{{ $card }}">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-6 p-4 rounded-12px border border-dashed border-d9">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="size-12 shrink-0 rounded-12px bg-primary/10 center">
                                <svg class="size-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <div class="min-w-0 text-start">
                                <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">الملفات والمرفقات</h2>
                                <p class="font-medium text-13px text-gray">أرفق ملفات لتظهر في ملفك الشخصي.</p>
                            </div>
                        </div>
                        <button type="button" class="font-semibold text-15px text-primary hover:opacity-80 shrink-0">+ إضافة ملف</button>
                    </div>
                    <div class="center flex-col py-10 text-center">
                        <span class="size-16 rounded-12px bg-primary/10 center mb-4">
                            <svg class="size-8 text-primary" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <p class="font-semibold text-17px text-primary mb-2">لا توجد مرفقات في الملف الشخصي</p>
                        <p class="font-medium text-14px text-gray">قم برفع ملفات لتظهر في ملفك الشخصي.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Tab 6: Login history ===================== --}}
    <div id="instructor-settings-6" class="hidden" role="tabpanel" aria-labelledby="instructor-settings-tab-6">
        <div class="border border-d9 rounded-14px bg-white overflow-hidden shadow-sm">
            <div class="px-5 sm:px-6 py-5 border-b border-d9">
                <h2 class="font-bold text-20px sm:text-22px text-primary text-start">سجل الدخول</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-15px sm:text-16px">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">نظام التشغيل</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">المتصفح</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">الجهاز</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">عنوان IP</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">الدولة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">المدينة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">بداية الجلسة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">نهاية الجلسة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">مدة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold whitespace-nowrap">اجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loginHistory ?? [] as $row)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-4 sm:px-5 py-4 font-medium text-primary whitespace-nowrap">{{ $row['os'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-primary whitespace-nowrap">{{ $row['browser'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-gray whitespace-nowrap">{{ $row['device'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-gray whitespace-nowrap max-w-40 truncate" title="{{ $row['ip'] }}">{{ $row['ip'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-primary whitespace-nowrap">{{ $row['country'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-primary whitespace-nowrap">{{ $row['city'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-primary whitespace-nowrap">{{ $row['session_start'] }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-gray whitespace-nowrap">{{ $row['session_end'] ?? '-' }}</td>
                                <td class="px-4 sm:px-5 py-4 font-medium text-gray whitespace-nowrap">{{ $row['duration'] }}</td>
                                <td class="px-4 sm:px-5 py-4 whitespace-nowrap">
                                    @if (!empty($row['active']))
                                        <button type="button" class="font-semibold text-14px text-red-500 hover:opacity-80">إنهاء الجلسة</button>
                                    @else
                                        <span class="text-gray">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-16 text-center font-medium text-16px text-gray">لا توجد جلسات مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-2">
        <button type="button"
            class="inline-flex items-center justify-center min-w-44 h-12 sm:h-14 px-8 rounded-10px bg-primary text-white font-semibold text-16px sm:text-18px hover:opacity-90 transition">
            حفظ التغييرات
        </button>
    </div>
</div>
@endsection
