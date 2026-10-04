@if(!empty($valid))
    <div class="rounded-16px border border-[#BBF7D0] bg-white overflow-hidden">
        <div class="bg-[#F0FDF4] px-5 py-4 flex items-center gap-3 border-b border-[#BBF7D0]">
            <div class="size-10 rounded-12px bg-[#0F4C45] center shrink-0">
                <span class="icon-[tabler--circle-check] size-6 text-white"></span>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-15px text-[#065F46]">شهادة صالحة — تم التحقق بنجاح</p>
                <p class="font-medium text-12px text-[#065F46]/70 truncate">البيانات مطابقة لسجلات مركز الجودة والتميز للتدريب</p>
            </div>
            <span class="mr-auto hidden sm:inline-flex items-center gap-1.5 rounded-full bg-[#0F4C45] text-white px-3 py-1 font-bold text-11px shrink-0">
                <span class="icon-[tabler--shield-check] size-3.5"></span> موثّقة
            </span>
        </div>

        <div class="p-5 space-y-0 divide-y divide-[#E8ECEA]">
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="font-semibold text-13px text-[#8E8F8F]">رقم الشهادة</span>
                <span class="font-mono font-bold text-15px text-primary" dir="ltr">{{ $number }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="font-semibold text-13px text-[#8E8F8F]">المتدرب</span>
                <span class="font-bold text-14px text-primary text-left">{{ $trainee }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="font-semibold text-13px text-[#8E8F8F]">النوع</span>
                <span class="inline-flex rounded-full px-3 py-1 font-bold text-11px
                    {{ ($type ?? '') === 'quiz' ? 'bg-[#EDE9FE] text-[#6D28D9]' : (($type ?? '') === 'bundle' ? 'bg-[#FEF3C7] text-[#92400E]' : 'bg-[#D1FAE5] text-[#065F46]') }}">
                    {{ $type_label ?? 'شهادة' }}
                </span>
            </div>
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="font-semibold text-13px text-[#8E8F8F]">الدورة</span>
                <span class="font-semibold text-13px text-primary text-left max-w-[65%]">{{ $course }}</span>
            </div>
            @if(!empty($hours))
                <div class="flex items-center justify-between gap-4 py-3">
                    <span class="font-semibold text-13px text-[#8E8F8F]">الساعات</span>
                    <span class="font-semibold text-13px text-primary">{{ $hours }} ساعة</span>
                </div>
            @endif
            @if(!empty($start_date) || !empty($end_date))
                <div class="flex items-center justify-between gap-4 py-3">
                    <span class="font-semibold text-13px text-[#8E8F8F]">فترة الدورة</span>
                    <span class="font-semibold text-13px text-primary" dir="ltr">{{ $start_date }}{{ !empty($end_date) ? ' – '.$end_date : '' }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between gap-4 py-3">
                <span class="font-semibold text-13px text-[#8E8F8F]">تاريخ الإصدار</span>
                <span class="font-semibold text-13px text-primary" dir="ltr">{{ $issue_date }}</span>
            </div>
        </div>

        @if(!empty($verify_url))
            <div class="px-5 pb-5">
                <div class="rounded-12px bg-[#FAF8F4] border border-[#E0D4BC]/40 px-4 py-3 flex items-start gap-3">
                    <span class="icon-[tabler--link] size-5 text-primary shrink-0 mt-0.5"></span>
                    <p class="font-medium text-12px text-[#7A8886] leading-relaxed break-all">
                        رابط التحقق:
                        <a href="{{ $verify_url }}" class="font-mono font-bold text-primary hover:underline" dir="ltr">{{ $verify_url }}</a>
                    </p>
                </div>
            </div>
        @endif
    </div>
@else
    <div class="rounded-16px border border-red-200 bg-white overflow-hidden">
        <div class="bg-red-50 px-5 py-6 text-center">
            <div class="size-14 rounded-16px bg-red-500 center mx-auto mb-3">
                <span class="icon-[tabler--circle-x] size-7 text-white"></span>
            </div>
            <p class="font-bold text-16px text-red-700">الشهادة غير صالحة</p>
            <p class="font-medium text-13px text-red-600/80 mt-1">لا يوجد سجل بهذا الرقم — تأكد من الإدخال أو تواصل مع الدعم</p>
        </div>
        <div class="p-5 text-center">
            <a href="{{ route('landing.v1.contact') }}" class="inline-flex items-center gap-2 rounded-12px border border-[#E8E8E8] bg-white px-5 h-10 font-semibold text-13px text-primary hover:bg-[#FAF8F4] transition">تواصل مع الدعم</a>
        </div>
    </div>
@endif
