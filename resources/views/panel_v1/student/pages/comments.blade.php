@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">تعليقاتي</h1>
            <p class="font-semibold text-20px text-gray">جميع تعليقاتك على الدورات</p>
        </div>

        @if (($comments ?? collect())->isEmpty())
            <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                <p class="font-bold text-20px text-gray">لا توجد تعليقات بعد</p>
                <p class="font-medium text-14px text-gray mt-2">شارك رأيك في الدورات التي حضرتها.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($comments as $comment)
                    <div class="border border-d9 rounded-16px bg-white px-6 py-5 flex gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-bold text-16px text-primary">{{ $comment->webinar->title ?? '' }}</p>
                                <span class="font-medium text-12px text-gray">— {{ date('Y/m/d H:i', (int) $comment->created_at) }}</span>
                            </div>
                            <p class="font-medium text-15px text-[#1A1A1A] leading-relaxed">{{ \Illuminate\Support\Str::limit(strip_tags($comment->comment ?? $comment->description ?? ''), 400) }}</p>
                            @if (!empty($comment->webinar))
                                <a href="{{ route('panel.v1.student.course.watch', ['slug' => $comment->webinar->slug]) }}" class="inline-flex mt-3 font-bold text-13px text-primary hover:underline">عرض الدورة</a>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2 shrink-0">
                            <details class="dropdown">
                                <summary class="font-bold text-13px text-primary cursor-pointer">تعديل</summary>
                                <form method="POST" action="{{ route('panel.v1.student.comments.update', ['id' => $comment->id]) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    <input type="text" name="comment" value="{{ $comment->comment }}" class="input input-bordered h-9 rounded-8px text-13px flex-1">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-8px text-12px">حفظ</button>
                                </form>
                            </details>
                            <details class="dropdown">
                                <summary class="font-bold text-13px text-[#D97706] cursor-pointer">إبلاغ</summary>
                                <form method="POST" action="{{ route('panel.v1.student.comments.report', ['id' => $comment->id]) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    <input type="text" name="reason" placeholder="السبب" class="input input-bordered h-9 rounded-8px text-13px flex-1">
                                    <button type="submit" class="btn btn-sm rounded-8px bg-[#FEF6E7] text-12px">إرسال</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('panel.v1.student.comments.delete', ['id' => $comment->id]) }}" onsubmit="return confirm('حذف التعليق؟');">
                                @csrf
                                <button type="submit" class="font-bold text-13px text-[#EF4444] hover:underline">حذف</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
