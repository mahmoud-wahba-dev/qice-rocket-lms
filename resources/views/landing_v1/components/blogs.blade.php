@extends('landing_v1.layouts.app')

@section('content')
    <main>
        <x-landing_v1::sec-header title="اخر الاخبار لدينا"
            sutitle="نخبة من الخبراء والمستشارين والمدربين المعتمدين يقودون مسارات التدريب في المركز بخبرات متنوعة في الحوكمة، الجودة، الإدارة الصحية، القانون، التدريب، التطوير المؤسسي والتميز." />

        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @for ($i = 0; $i < 12; $i++)
                        <x-landing_v1::blog-card />
                    @endfor
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
