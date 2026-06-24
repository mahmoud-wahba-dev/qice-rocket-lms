@extends('landing_v1.layouts.app')

@section('content')
    <main>
        <x-landing_v1::sec-header
            title="قائمة المدربين بالمركز"
            subtitle="نخبة من الخبراء والمستشارين والمدربين المعتمدين يقودون مسارات التدريب في المركز بخبرات متنوعة في الحوكمة، الجودة، الإدارة الصحية، القانون، التدريب، التطوير المؤسسي والتميز"
        />

        <section class="pb-8 lg:pb-32 my-0">
            <div class="container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @forelse ($instructors as $instructor)
                        <x-landing_v1::instructor-card
                            :name="$instructor->full_name"
                            :avatar="$instructor->getAvatar()"
                            :bio="$instructor->about ?? $instructor->bio ?? $instructor->headline ?? ''"
                            :username="$instructor->username"
                        />
                    @empty
                        <p class="col-span-full text-center font-medium text-primary/70 py-12">لا يوجد مدربون متاحون حالياً.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
