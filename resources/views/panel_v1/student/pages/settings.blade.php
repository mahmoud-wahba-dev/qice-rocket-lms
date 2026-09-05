@extends('panel_v1.student.layouts.app')

@section('content')
@php
    $toggleClass = 'peer sr-only';
    $toggleTrack = 'relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full bg-d9 transition peer-checked:bg-[#0FC787] after:absolute after:top-0.5 after:start-0.5 after:size-6 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:after:translate-x-[-1.25rem] rtl:peer-checked:after:translate-x-[-1.25rem]';
@endphp

<section class="pt-28 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">اعدادات الحساب</h1>
            <p class="font-semibold text-24px text-gray">إدارة إعدادات حسابك</p>
        </div>

        <nav class="student-dash-tabs tabs tabs-bordered tabs-lg w-full overflow-x-auto mb-12"
            aria-label="أقسام إعدادات الحساب" role="tablist" aria-orientation="horizontal">
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 active whitespace-nowrap"
                id="settings-tabs-item-1" data-tab="#settings-tabs-1" aria-controls="settings-tabs-1" role="tab"
                aria-selected="true">
                معلومات أساسية
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-2" data-tab="#settings-tabs-2" aria-controls="settings-tabs-2" role="tab"
                aria-selected="false">
                معلومات اضافية
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-3" data-tab="#settings-tabs-3" aria-controls="settings-tabs-3" role="tab"
                aria-selected="false">
                الهوية والمالية
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-4" data-tab="#settings-tabs-4" aria-controls="settings-tabs-4" role="tab"
                aria-selected="false">
                الصور
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-5" data-tab="#settings-tabs-5" aria-controls="settings-tabs-5" role="tab"
                aria-selected="false">
                حول
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-20px md:text-24px text-gray active-tab:text-primary pb-5 whitespace-nowrap"
                id="settings-tabs-item-6" data-tab="#settings-tabs-6" aria-controls="settings-tabs-6" role="tab"
                aria-selected="false">
                سجل الدخول
            </button>
        </nav>

        <div>
            <div id="settings-tabs-1" role="tabpanel" aria-labelledby="settings-tabs-item-1">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {{-- Main column --}}
                    <div class="lg:col-span-7 flex flex-col gap-8">
                        <div class="border border-d9 rounded-20px bg-white px-8 py-8">
                            <h2 class="font-bold text-24px text-primary mb-8">الحساب والأمان</h2>

                            <div class="space-y-7">
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الاسم</label>
                                    <input type="text" value="{{ $authUser->full_name ?? '' }}"
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">البريد الإلكتروني</label>
                                    <input type="email" value="{{ $authUser->email ?? '' }}"
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">هاتف</label>
                                    <input type="text" value="{{ $authUser->mobile ?? '' }}"
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">كلمة المرور</label>
                                    <input type="password" value=""
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">اعد كتابة كلمة المرور</label>
                                    <input type="password" value=""
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">كلمة المرور الحالية</label>
                                    <input type="password" value=""
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <div class="border border-d9 rounded-20px bg-white px-8 py-8">
                            <h2 class="font-bold text-24px text-primary mb-8">خيارات الحساب</h2>
                            <div class="divide-y divide-d9">
                                @foreach (range(1, 5) as $i)
                                    <div class="flex items-center justify-between gap-4 py-5 first:pt-0 last:pb-0">
                                        <p class="font-medium text-16px text-black">تفعيل وضع الاجازة</p>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="{{ $toggleClass }}">
                                            <span class="{{ $toggleTrack }}"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Side column --}}
                    <div class="lg:col-span-5 flex flex-col gap-8">
                        <div class="border border-d9 rounded-20px bg-white px-8 py-8">
                            <h2 class="font-bold text-24px text-primary mb-8">التعريب</h2>
                            <div class="space-y-7">
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">لغة</label>
                                    <input type="text" value="العربية"
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                                <div class="relative">
                                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">المنطقة الزمنية</label>
                                    <input type="text" value="Asia/Riyadh"
                                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <div class="border border-d9 rounded-20px bg-white px-8 py-8">
                            <h2 class="font-bold text-24px text-primary mb-8">وضع الاجازة</h2>
                            <div class="flex items-center justify-between gap-4 mb-7">
                                <p class="font-medium text-16px text-black">تفعيل وضع الاجازة</p>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="{{ $toggleClass }}">
                                    <span class="{{ $toggleTrack }}"></span>
                                </label>
                            </div>
                            <div class="relative">
                                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">رسالة وضع الإجازة</label>
                                <textarea rows="5"
                                    class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-32 resize-y"
                                    placeholder=""></textarea>
                            </div>
                        </div>

                        <div class="border border-d9 rounded-20px bg-white min-h-40"></div>
                    </div>
                </div>
            </div>

            @foreach ([
                2 => 'معلومات اضافية',
                3 => 'الهوية والمالية',
                4 => 'الصور',
                5 => 'حول',
                6 => 'سجل الدخول',
            ] as $tabId => $tabLabel)
                <div id="settings-tabs-{{ $tabId }}" class="hidden" role="tabpanel"
                    aria-labelledby="settings-tabs-item-{{ $tabId }}">
                    <div class="border border-d9 rounded-20px bg-white px-8 py-20 center flex-col text-center">
                        <p class="font-semibold text-24px text-gray">{{ $tabLabel }}</p>
                        <p class="font-medium text-16px text-gray mt-3">سيتم إضافة محتوى هذا القسم قريباً.</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
