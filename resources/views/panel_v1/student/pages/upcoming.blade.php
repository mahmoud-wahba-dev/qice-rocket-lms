@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">الدورات القادمة</h1>
            <p class="font-semibold text-20px text-gray">تابع الدورات التي ستنطلق قريباً</p>
        </div>

        @if (($upcomingCourses ?? collect())->isEmpty())
            <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                <p class="font-bold text-20px text-gray">لا توجد دورات قادمة حالياً</p>
                <p class="font-medium text-14px text-gray mt-2">تابع هذه الصفحة لمعرفة الجديد.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($upcomingCourses as $course)
                    @php $isFollowing = in_array($course->id, $followings ?? []); @endphp
                    <div class="border border-d9 rounded-16px bg-white p-6">
                        <h3 class="font-bold text-18px text-primary mb-1">{{ $course->title }}</h3>
                        <p class="font-medium text-13px text-gray mb-4">{{ $course->category->title ?? '' }} — {{ date('Y/m/d', (int) $course->created_at) }}</p>
                        <p class="font-medium text-14px text-gray leading-relaxed mb-5">{{ \Illuminate\Support\Str::limit(strip_tags($course->description ?? ''), 140) }}</p>
                        @if ($isFollowing)
                            <form method="POST" action="{{ route('panel.v1.student.upcoming.unfollow', ['id'=>$course->id]) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost rounded-10px h-10 px-5 font-bold text-13px border border-d9">إلغاء المتابعة</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('panel.v1.student.upcoming.follow', ['id'=>$course->id]) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary rounded-10px h-10 px-5 font-bold text-13px">متابعة</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
