@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('panel.v1.student.support') }}" class="inline-flex items-center gap-2 font-bold text-14px text-primary hover:underline"><span class="icon-[tabler--arrow-right] size-4"></span> العودة للدعم</a>
                <h1 class="font-extrabold text-26px text-primary mt-3">{{ $ticket->title ?? '' }}</h1>
                <p class="font-medium text-14px text-gray mt-1">
                    {{ $ticket->status === 'open' ? 'مفتوحة' : ($ticket->status === 'close' ? 'مغلقة' : 'تم الرد') }} — {{ date('Y/m/d H:i', (int) $ticket->created_at) }}
                    @if (!empty($ticket->department)) • {{ $ticket->department->title }} @endif
                    @if (!empty($ticket->webinar)) • {{ $ticket->webinar->title }} @endif
                </p>
            </div>
            @if (($ticket->status ?? '') !== 'close')
                <form method="POST" action="{{ route('panel.v1.student.support.close', ['id' => $ticket->id]) }}" onsubmit="return confirm('إغلاق التذكرة؟');">
                    @csrf
                    <button type="submit" class="btn btn-ghost rounded-10px h-11 px-5 font-bold text-14px text-[#EF4444] border border-[#FECACA]">إغلاق التذكرة</button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <div class="border border-d9 rounded-16px bg-white overflow-hidden">
                    <div class="max-h-[480px] overflow-y-auto p-6 space-y-4">
                        @forelse ($ticket->conversations ?? [] as $conv)
                            @php $isMine = (int) ($conv->sender_id ?? 0) === (int) ($authUser->id ?? 0); @endphp
                            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[80%] rounded-16px px-5 py-4 {{ $isMine ? 'bg-primary text-white' : 'bg-fa border border-d9' }}">
                                    <p class="font-medium text-14px leading-relaxed">{{ $conv->message }}</p>
                                    @if (!empty($conv->attach))
                                        <a href="{{ $conv->attach }}" target="_blank" class="inline-flex mt-2 font-bold text-12px {{ $isMine ? 'text-white underline' : 'text-primary underline' }}">مرفق</a>
                                    @endif
                                    <p class="font-medium text-11px mt-2 {{ $isMine ? 'text-white/70' : 'text-gray' }}">{{ date('Y/m/d H:i', (int) $conv->created_at) }} — {{ $isMine ? 'أنت' : ($conv->sender->full_name ?? 'الدعم') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="font-medium text-14px text-gray text-center py-8">لا توجد رسائل بعد.</p>
                        @endforelse
                    </div>

                    @if (($ticket->status ?? '') !== 'close')
                        <form method="POST" action="{{ route('panel.v1.student.support.reply', ['id' => $ticket->id]) }}" enctype="multipart/form-data" class="border-t border-d9 p-5 bg-fa/30">
                            @csrf
                            <textarea name="message" rows="4" required placeholder="اكتب ردك هنا..." class="textarea textarea-bordered w-full rounded-12px border-d9 font-medium text-15px focus:outline-none focus:border-primary min-h-24"></textarea>
                            <div class="flex items-center justify-between gap-4 mt-4">
                                <label class="flex items-center gap-2 cursor-pointer font-medium text-13px text-gray">
                                    <input type="file" name="attach" class="hidden">
                                    <span class="icon-[tabler--paperclip] size-4"></span> إرفاق ملف
                                </label>
                                <button type="submit" class="btn btn-primary rounded-10px h-11 px-8 font-bold text-14px">إرسال الرد</button>
                            </div>
                        </form>
                    @else
                        <div class="border-t border-d9 p-5 bg-[#FEF2F2] text-center">
                            <p class="font-bold text-14px text-[#B91C1C]">التذكرة مغلقة — لا يمكن الرد.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="border border-d9 rounded-16px bg-white p-6">
                    <h3 class="font-bold text-16px text-primary mb-4">تفاصيل التذكرة</h3>
                    <div class="space-y-3 font-medium text-14px">
                        <div class="flex justify-between"><span class="text-gray">الرقم</span><span class="font-bold text-primary">#{{ $ticket->id }}</span></div>
                        <div class="flex justify-between"><span class="text-gray">الحالة</span><span class="font-bold {{ $ticket->status==='close' ? 'text-gray' : 'text-[#00B31B]' }}">{{ $ticket->status }}</span></div>
                        <div class="flex justify-between"><span class="text-gray">التاريخ</span><span class="font-bold text-primary">{{ date('Y/m/d', (int) $ticket->created_at) }}</span></div>
                    </div>
                    <a href="{{ route('panel.v1.student.support') }}" class="btn btn-ghost w-full rounded-10px h-11 mt-6 font-bold text-14px">العودة للقائمة</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
