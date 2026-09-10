@extends('panel_v1.organization.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">دورات المنظمة</h1>
            <p class="font-semibold text-24px text-gray">الدورات المنشأة بواسطة المنظمة ومدربيها</p>
        </div>

        <div class="flex flex-col gap-4">
            @forelse ($courses ?? [] as $course)
                <div class="border border-d9 rounded-16px bg-white px-8 py-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-20px text-primary mb-1">{{ $course['title'] }}</p>
                        <p class="font-medium text-14px text-gray">{{ $course['subtitle'] }} — {{ $course['students'] }} متدرب</p>
                    </div>
                    <span
                        class="font-bold text-14px px-4 py-2 rounded-8px shrink-0 {{ $course['status'] === 'active' ? 'bg-[#E8F5E9] text-[#00B31B]' : 'bg-fa text-gray' }}">
                        {{ $course['status'] === 'active' ? 'نشطة' : $course['status'] }}
                    </span>
                </div>
            @empty
                <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                    <p class="font-semibold text-24px text-gray">لا توجد دورات بعد</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
