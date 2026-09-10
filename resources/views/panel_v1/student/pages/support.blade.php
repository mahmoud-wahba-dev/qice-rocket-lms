@extends('panel_v1.student.layouts.app')

@section('content')
<section class="">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">الدعم</h1>
            <p class="font-semibold text-24px text-gray">تواصل مع الدعم الخاص بنا</p>
        </div>

        <nav class="student-dash-tabs tabs tabs-bordered tabs-lg w-full md:w-[75%] overflow-x-auto mb-12"
            aria-label="أقسام الدعم" role="tablist" aria-orientation="horizontal">
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5 active"
                id="support-tabs-item-1" data-tab="#support-tabs-1" aria-controls="support-tabs-1" role="tab"
                aria-selected="true">
                تواصل مع الدعم
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="support-tabs-item-2" data-tab="#support-tabs-2" aria-controls="support-tabs-2" role="tab"
                aria-selected="false">
                تذاكر الدعم
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="support-tabs-item-3" data-tab="#support-tabs-3" aria-controls="support-tabs-3" role="tab"
                aria-selected="false">
                دعم الدورات
            </button>
        </nav>

        <div>
            <div id="support-tabs-1" role="tabpanel" aria-labelledby="support-tabs-item-1">
                <div
                    class="grid grid-cols-1 lg:grid-cols-12 overflow-hidden rounded-20px border border-d9 bg-white shadow-[0_8px_30px_rgba(15,76,69,0.08)]">

                    {{-- Form --}}
                    <div class="lg:col-span-8 px-8 py-10 lg:px-12 lg:py-12 ">
                        <h2 class="font-bold text-32px text-black mb-10">رسالة دعم جديدة</h2>

                        <form class="space-y-8" action="{{ route('panel.v1.student.support.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="platform_support">
                            <div class="relative">
                                <label for="support-type"
                                    class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                                    اختر قسم الدعم
                                </label>
                                <select id="support-type" name="department_id"
                                    class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                                    <option selected disabled value="">القسم</option>
                                    @foreach ($departments ?? [] as $department)
                                        <option value="{{ $department->id }}">{{ $department->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="relative">
                                <label for="support-subject"
                                    class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                                    عنوان الموضوع
                                </label>
                                <input id="support-subject" name="title" type="text" value="{{ old('title') }}"
                                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary"
                                    placeholder="">
                            </div>

                            <div class="relative">
                                <label for="support-message"
                                    class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-semibold text-14px text-gray">
                                    الرسالة
                                </label>
                                <textarea id="support-message" name="message" rows="8"
                                    class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-48 resize-y"
                                    placeholder=""></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-10px h-14 w-full font-bold text-18px">
                                ارسل الرسالة
                            </button>
                        </form>
                    </div>
                    {{-- Brand sidebar --}}
                    <aside
                        class="lg:col-span-4 bg-primary text-white px-10 py-14 flex flex-col items-center justify-center text-center gap-10 relative overflow-hidden ">
                        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                            <div class="absolute -top-20 -start-16 size-56 rounded-full bg-primary blur-[97px]"></div>
                            <div class="absolute -bottom-20 -end-16 size-56 rounded-full bg-primary blur-[97px]">
                            </div>
                        </div>

                        <div class="relative w-40 h-40">
                            <img src="{{ $landingImg }}/logo-footer.webp" alt="QIEC Training"
                                class="size-full object-contain brightness-0 invert" loading="lazy" decoding="async">
                        </div>

                        <div class="relative flex items-center justify-center gap-3">
                            <a href="#" aria-label="WhatsApp"
                                class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                                <span class="icon-[tabler--brand-whatsapp] size-7"></span>
                            </a>
                            <a href="#" aria-label="Telegram"
                                class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                                <span class="icon-[tabler--brand-telegram] size-7"></span>
                            </a>
                            <a href="#" aria-label="Instagram"
                                class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                                <span class="icon-[tabler--brand-instagram] size-7"></span>
                            </a>
                            <a href="#" aria-label="LinkedIn"
                                class="size-12 rounded-full bg-white/10 center text-white hover:bg-white/20 transition">
                                <span class="icon-[tabler--brand-linkedin] size-7"></span>
                            </a>



                        </div>

                        <p class="relative font-bold text-22px text-white leading-relaxed max-w-xs">
                            نسعد جداً بتواصلكم معنا سوف يتم الرد عليكم قريباً ...
                        </p>
                    </aside>




                </div>
            </div>

            <div id="support-tabs-2" class="hidden" role="tabpanel" aria-labelledby="support-tabs-item-2">
                @forelse ($tickets ?? [] as $ticket)
                    <div class="border border-d9 rounded-16px bg-white px-8 py-5 mb-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-20px text-primary mb-1">{{ $ticket->title }}</p>
                            <p class="font-medium text-14px text-gray">
                                {{ $ticket->status === 'open' ? 'مفتوحة' : ($ticket->status === 'close' ? 'مغلقة' : 'تم الرد') }}
                                —
                                {{ date('Y/m/d', (int) $ticket->created_at) }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ url('/panel/support/' . $ticket->id . '/conversations') }}"
                                class="font-bold text-14px px-4 py-2 rounded-8px bg-[#E8F5E9] text-[#00B31B]">
                                عرض والرد
                            </a>
                            <span
                                class="font-bold text-14px px-4 py-2 rounded-8px {{ $ticket->status === 'open' ? 'bg-[#E8F5E9] text-[#00B31B]' : 'bg-fa text-gray' }}">
                                {{ $ticket->status === 'open' ? 'مفتوحة' : 'مغلقة' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="border border-d9 rounded-20px bg-white px-8 py-20 center flex-col text-center">
                        <p class="font-semibold text-24px text-gray">تذاكر الدعم</p>
                        <p class="font-medium text-16px text-gray mt-3">لا توجد تذاكر بعد — أرسل رسالتك من تبويب التواصل.</p>
                    </div>
                @endforelse
            </div>

            <div id="support-tabs-3" class="hidden" role="tabpanel" aria-labelledby="support-tabs-item-3">
                @forelse ($courseTickets ?? [] as $ticket)
                    <div class="border border-d9 rounded-16px bg-white px-8 py-5 mb-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-20px text-primary mb-1">{{ $ticket->title }}</p>
                            <p class="font-medium text-14px text-gray">
                                {{ optional($ticket->webinar)->title ?? '' }} —
                                {{ date('Y/m/d', (int) $ticket->created_at) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="border border-d9 rounded-20px bg-white px-8 py-20 center flex-col text-center">
                        <p class="font-semibold text-24px text-gray">دعم الدورات</p>
                        <p class="font-medium text-16px text-gray mt-3">لا توجد طلبات دعم مرتبطة بدورات حالياً.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
