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
                    @for ($i = 0; $i < 12; $i++)
                        <x-landing_v1::instructor-card />
                    @endfor
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
