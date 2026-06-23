@extends('landing_v1.layouts.app')

@section('content')
    <main>
        <x-landing_v1::sec-header
            title="ورش ومحاضرات مجانية لتطوير مهاراتك المهنية"
            subtitle="مجموعة من الورش التدريبية والمحاضرات المجانية المصممة لرفع الوعي المهني ومساعدة المتدربين على اختيار المسار المهني الأنسب"
        />

        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @for ($i = 0; $i < 12; $i++)
                        <x-landing_v1::workshop-card />
                    @endfor
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
