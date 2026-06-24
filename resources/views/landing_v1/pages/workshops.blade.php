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
                    @forelse ($workshops as $workshop)
                        <x-landing_v1::workshop-card
                            :title="$workshop->title"
                            :summary="$workshop->summary ?? $workshop->description"
                            :categoryTitle="$workshop->category->title ?? ''"
                            :slug="$workshop->slug"
                        />
                    @empty
                        <p class="col-span-full text-center font-medium text-primary/70 py-12">لا توجد ورش مجانية متاحة حالياً.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
