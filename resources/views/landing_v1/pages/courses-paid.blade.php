@extends('landing_v1.layouts.app')

@section('content')
    <main>
        <section class="pt-8 lg:pt-20 pb-8 lg:pb-12 my-0 relative overflow-visible">
            <div class="container relative z-10">
                <h2 class="font-bold text-38px text-primary mb-4 text-center">الدورات المعتمدة والبرامج المدفوعة</h2>
                <p class="font-normal text-base text-primary/70 text-center max-w-4xl mx-auto">
                    برامج تدريبية احترافية ومعتمدة في مختلف المجالات
                </p>
            </div>
        </section>

        @if ($categories->isNotEmpty())
            <div class="container mb-8 lg:mb-12">
                <div class="flex justify-center">
                    <nav class="paid-courses-filter flex max-w-full flex-wrap justify-center gap-2 overflow-x-auto px-1 pb-1 lg:gap-3"
                        aria-label="تصفية الدورات حسب التصنيف" role="tablist">
                        <button type="button" role="tab"
                            aria-selected="{{ empty($activeCategory) ? 'true' : 'false' }}"
                            data-category-id=""
                            class="paid-courses-filter__pill paid-courses-category-btn {{ empty($activeCategory) ? 'is-active' : '' }}">
                            الكل
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" role="tab"
                                aria-selected="{{ (string) $activeCategory === (string) $category->id ? 'true' : 'false' }}"
                                data-category-id="{{ $category->id }}"
                                class="paid-courses-filter__pill paid-courses-category-btn {{ (string) $activeCategory === (string) $category->id ? 'is-active' : '' }}">
                                {{ $category->title }}
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>
        @endif

        <section class="mb-8 lg:mb-14 mt-0" id="courses-paid-results">
            <div class="container relative min-h-[200px]">
                <div id="courses-paid-loader"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 opacity-0 pointer-events-none transition-opacity duration-150">
                    <span class="loading loading-spinner text-primary size-10"></span>
                </div>

                <div id="courses-paid-container">
                    @include('landing_v1.pages.courses_paid_list', [
                        'courses' => $courses,
                        'activeCategory' => $activeCategory,
                    ])
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('courses-paid-loader');
    const container = document.getElementById('courses-paid-container');
    const filterBtns = document.querySelectorAll('.paid-courses-category-btn');
    const baseUrl = @json(route('landing.v1.courses-paid'));

    if (!loader || !container || !filterBtns.length) {
        return;
    }

    let activeCategoryId = @json($activeCategory ?? '');
    let fetchController = null;

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const categoryId = this.getAttribute('data-category-id') || '';

            if (categoryId === activeCategoryId) {
                return;
            }

            activeCategoryId = categoryId;

            filterBtns.forEach(function (b) {
                const isActive = (b.getAttribute('data-category-id') || '') === categoryId;
                b.classList.toggle('is-active', isActive);
                b.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            fetchCourses(categoryId);
        });
    });

    function fetchCourses(categoryId) {
        if (fetchController) {
            fetchController.abort();
        }
        fetchController = new AbortController();

        loader.classList.remove('opacity-0', 'pointer-events-none');

        const params = new URLSearchParams();
        if (categoryId) {
            params.set('category_id', categoryId);
        }

        const url = params.toString() ? baseUrl + '?' + params.toString() : baseUrl;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: fetchController.signal,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network error');
                }
                return response.json();
            })
            .then(function (data) {
                container.innerHTML = data.html;
                const newUrl = params.toString() ? baseUrl + '?' + params.toString() : baseUrl;
                history.replaceState(null, '', newUrl);
            })
            .catch(function (err) {
                if (err.name !== 'AbortError') {
                    console.error('Error fetching courses:', err);
                }
            })
            .finally(function () {
                loader.classList.add('opacity-0', 'pointer-events-none');
                fetchController = null;
            });
    }
});
</script>
@endpush
