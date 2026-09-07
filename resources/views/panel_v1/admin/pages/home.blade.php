@extends('panel_v1.admin.layouts.app')

@section('content')
    <div class="border border-d9 rounded-14px bg-white px-6 py-16 text-center">
        <p class="font-semibold text-20px text-primary mb-2">لوحة الإدارة</p>
        <p class="font-medium text-15px text-gray mb-6">سيتم تحويلك إلى لوحة التعليم والأكاديميات.</p>
        <a href="{{ route('panel.v1.admin.education.home') }}"
            class="inline-flex h-11 px-5 rounded-12px bg-primary text-white font-semibold text-15px items-center">
            الانتقال الآن
        </a>
    </div>
@endsection
