@extends('panel_v1.organization.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">لوحة المنظمة</h1>
            <p class="font-semibold text-24px text-gray">مرحباً {{ $authUser->full_name ?? '' }} — نظرة عامة على منظمتك</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @foreach ($stats ?? [] as $stat)
                <div class="border border-d9 rounded-16px bg-white px-8 py-8 text-center">
                    <p class="font-bold text-40px text-primary leading-none mb-3">{{ $stat['value'] }}</p>
                    <p class="font-semibold text-18px text-gray">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('panel.v1.organization.users', ['type' => 'instructors']) }}"
                class="border border-d9 rounded-16px bg-white px-8 py-8 font-bold text-20px text-primary hover:opacity-80 transition text-center">
                إدارة المدربين
            </a>
            <a href="{{ route('panel.v1.organization.courses') }}"
                class="border border-d9 rounded-16px bg-white px-8 py-8 font-bold text-20px text-primary hover:opacity-80 transition text-center">
                دورات المنظمة
            </a>
        </div>
    </div>
</section>
@endsection
