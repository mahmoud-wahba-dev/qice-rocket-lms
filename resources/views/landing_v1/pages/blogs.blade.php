@extends('landing_v1.layouts.app')
@section('content')
    <main>
        <x-landing_v1::sec-header title="اخر الاخبار لدينا"
            subtitle="نخبة من الخبراء والمستشارين والمدربين المعتمدين يقودون مسارات التدريب في المركز بخبرات متنوعة في الحوكمة، الجودة، الإدارة الصحية، القانون، التدريب، التطوير المؤسسي والتميز." />

        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @forelse ($posts as $post)
                        <x-landing_v1::blog-card
                            :title="$post->title"
                            :image="$post->image ?? asset('assets/landing_v1/img/home/news1.webp')"
                            :slug="$post->slug"
                        />
                    @empty
                        <p class="col-span-full text-center font-medium text-primary/70 py-12">لا توجد مقالات متاحة حالياً.</p>
                    @endforelse
                </div>
                @if ($posts->hasPages())
                    <div class="mt-10 flex justify-center">
                        {{ $posts->withQueryString()->links('vendor.pagination.landing_v1') }}
                    </div>
                @endif
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
