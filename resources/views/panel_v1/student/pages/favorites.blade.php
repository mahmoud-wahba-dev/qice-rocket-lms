@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">المفضلة</h1>
            <p class="font-semibold text-20px text-gray">الدورات التي حفظتها للرجوع إليها</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse ($favorites ?? [] as $favorite)
                <div class="border border-d9 rounded-16px bg-white px-8 py-6 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-20px text-primary mb-1">{{ $favorite->webinar->title ?? '' }}</p>
                        <p class="font-medium text-14px text-gray">{{ $favorite->webinar->category->title ?? '' }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if (!empty($favorite->webinar))
                            <a href="{{ route('panel.v1.student.course.watch', ['slug' => $favorite->webinar->slug]) }}"
                                class="font-bold text-14px px-4 py-2 rounded-8px bg-[#E8F5E9] text-[#00B31B]">عرض</a>
                        @endif
                        <form method="POST" action="{{ route('panel.v1.student.favorites.toggle') }}">
                            @csrf
                            <input type="hidden" name="webinar_id" value="{{ $favorite->webinar_id }}">
                            <button type="submit" class="font-bold text-14px px-4 py-2 rounded-8px bg-fa text-gray">إزالة</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                    <p class="font-semibold text-24px text-gray">لا توجد عناصر في المفضلة</p>
                    <a href="{{ route('landing.v1.courses') }}" class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px mt-6">تصفح الدورات</a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
