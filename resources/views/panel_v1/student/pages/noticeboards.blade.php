@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-extrabold text-36px text-primary mb-3">إعلانات الدورات</h1>
                <p class="font-semibold text-20px text-gray">إعلانات المدربين والمنصة الخاصة بدوراتك</p>
            </div>
            @if (($unreadCount ?? 0) > 0)
                <span class="rounded-12px bg-[#FEF6E7] border border-[#F5D9A8]/60 px-5 py-3 font-bold text-14px text-[#7A4A00]">{{ $unreadCount }} غير مقروء</span>
            @endif
        </div>

        @if (($noticeboards ?? collect())->isEmpty())
            <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                <p class="font-bold text-20px text-gray">لا توجد إعلانات حالياً</p>
                <p class="font-medium text-14px text-gray mt-2">ستظهر هنا إعلانات مدربيك.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($noticeboards as $board)
                    <div class="border border-d9 rounded-16px bg-white px-6 py-5 flex gap-4 items-start">
                        <div class="size-12 rounded-12px center shrink-0" style="background-color: {{ $board->color ?? '#0f4c45' }}20; border: 1px solid {{ $board->color ?? '#0f4c45' }}30">
                            <span class="icon-[tabler--bell] size-6" style="color: {{ $board->color ?? '#0f4c45' }}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-18px text-primary mb-1">{{ $board->title }}</p>
                            <p class="font-medium text-14px text-gray leading-relaxed">{{ $board->message }}</p>
                            <div class="flex items-center gap-3 mt-3 font-medium text-12px text-gray">
                                <span>{{ $board->webinar->title ?? ($board->type ?? '') }}</span>
                                <span>•</span>
                                <span>{{ date('Y/m/d H:i', (int) $board->created_at) }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('panel.v1.student.noticeboards.seen', ['id'=>$board->id]) }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="font-bold text-13px text-primary hover:underline">مقروء</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
