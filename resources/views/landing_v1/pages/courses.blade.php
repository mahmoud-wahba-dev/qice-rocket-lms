@extends('landing_v1.layouts.app')

@section('content')
    @php($landingImg = asset('assets/landing_v1/img/instructors'))
    <main>
        <x-landing_v1::page-hero image="{{ asset('assets/landing_v1/img/contact/hero.webp') }}" title="استكشف مكتبة المعرفة"
            :breadcrumbs="[
                ['label' => 'الرئيسية', 'href' => route('landing.v1.index')],
                ['label' => 'الدورات', 'href' => route('landing.v1.courses')],
            ]" />

        <section class="section-gap">
            <div class="container">

                <div
                    class="flex items-center justify-between flex-wrap px-10 py-6 mb-20 border border-[#CCCCCC] rounded-8px">
                    <nav class="tabs --prevent-on-load-init gap-2 overflow-x-auto whitespace-nowrap" aria-label="Tabs"
                        aria-orientation="horizontal">
                        <button type="button"
                            class="category-tab-btn btn btn-text font-medium text-22px text-primary active-tab:bg-primary active-tab:text-white hover:text-primary hover:bg-primary/20 active bg-primary text-white"
                            data-category-id="">
                            جميع المجالات
                        </button>
                        @foreach ($categories as $category)
                            <button type="button"
                                class="category-tab-btn btn btn-text font-medium text-22px text-primary active-tab:bg-primary active-tab:text-white hover:text-primary hover:bg-primary/20"
                                data-category-id="{{ $category->id }}">
                                {{ $category->title }}
                            </button>
                        @endforeach
                    </nav>
                    <div class="dropdown relative inline-flex rtl:[--placement:bottom-end]">
                        <button id="dropdown-default" type="button"
                            class="dropdown-toggle btn h-14 btn-outline px-8 border border-[#CECECE]" aria-haspopup="menu"
                            aria-expanded="false" aria-label="Dropdown">
                            <svg width="21" height="19" viewBox="0 0 21 19" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20.3095 2.97619H6.69048M4.21429 2.97619H0.5M20.3095 15.3571H6.69048M4.21429 15.3571H0.5M14.119 9.16667H0.5M20.3095 9.16667H16.5952M5.45238 0.5C5.78074 0.5 6.09566 0.630442 6.32785 0.86263C6.56003 1.09482 6.69048 1.40973 6.69048 1.7381V4.21429C6.69048 4.54265 6.56003 4.85756 6.32785 5.08975C6.09566 5.32194 5.78074 5.45238 5.45238 5.45238C5.12402 5.45238 4.8091 5.32194 4.57692 5.08975C4.34473 4.85756 4.21429 4.54265 4.21429 4.21429V1.7381C4.21429 1.40973 4.34473 1.09482 4.57692 0.86263C4.8091 0.630442 5.12402 0.5 5.45238 0.5ZM5.45238 12.881C5.78074 12.881 6.09566 13.0114 6.32785 13.2436C6.56003 13.4758 6.69048 13.7907 6.69048 14.119V16.5952C6.69048 16.9236 6.56003 17.2385 6.32785 17.4707C6.09566 17.7029 5.78074 17.8333 5.45238 17.8333C5.12402 17.8333 4.8091 17.7029 4.57692 17.4707C4.34473 17.2385 4.21429 16.9236 4.21429 16.5952V14.119C4.21429 13.7907 4.34473 13.4758 4.57692 13.2436C4.8091 13.0114 5.12402 12.881 5.45238 12.881ZM15.3571 6.69048C15.6855 6.69048 16.0004 6.82092 16.2326 7.05311C16.4648 7.28529 16.5952 7.60021 16.5952 7.92857V10.4048C16.5952 10.7331 16.4648 11.048 16.2326 11.2802C16.0004 11.5124 15.6855 11.6429 15.3571 11.6429C15.0288 11.6429 14.7139 11.5124 14.4817 11.2802C14.2495 11.048 14.119 10.7331 14.119 10.4048V7.92857C14.119 7.60021 14.2495 7.28529 14.4817 7.05311C14.7139 6.82092 15.0288 6.69048 15.3571 6.69048Z"
                                    stroke="#747474" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            تصفية
                        </button>
                        <div class="dropdown-menu dropdown-open:opacity-100 hidden w-[340px] p-6 rounded-8px border border-[#E5E5E5] shadow-lg bg-white"
                            role="menu" aria-orientation="vertical" aria-labelledby="dropdown-false">
                            <div class="space-y-6">
                                <!-- Sort By -->
                                <div>
                                    <h4 class="font-bold text-20px mb-4 text-black">الترتيب حسب</h4>
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-sort-val="popular"
                                            class="sort-btn flex-1 py-2.5 text-center text-16px font-bold rounded-8px transition-colors bg-[#EBEBEB] text-black hover:bg-gray-100">
                                            الأشهر
                                        </button>
                                        <button type="button" data-sort-val="latest"
                                            class="sort-btn flex-1 py-2.5 text-center text-16px font-bold rounded-8px transition-colors bg-primary text-white">
                                            الأحدث
                                        </button>
                                        <button type="button" data-sort-val="oldest"
                                            class="sort-btn flex-1 py-2.5 text-center text-16px font-bold rounded-8px transition-colors bg-[#EBEBEB] text-black hover:bg-gray-100">
                                            الأقدم
                                        </button>
                                    </div>
                                </div>

                                <hr class="border-gray-200">

                                <!-- Course Type -->
                                <div>
                                    <h4 class="font-medium text-16px text-gray-500 mb-4">نوع الدورة</h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" value="paid" class="filter-type-checkbox form-checkbox w-5 h-5 border-gray-400 rounded-sm text-gold focus:ring-gold focus:ring-offset-0 bg-transparent">
                                            <span class="text-16px text-gray-500 mx-3 font-medium">مسبق الدفع</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" value="free_with_paid_cert" class="filter-type-checkbox form-checkbox w-5 h-5 border-gray-400 rounded-sm text-gold focus:ring-gold focus:ring-offset-0 bg-transparent">
                                            <span class="text-16px text-gray-500 mx-3 font-medium">مجانية بشهادة مدفوعة</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" value="free" class="filter-type-checkbox form-checkbox w-5 h-5 border-gray-400 rounded-sm text-gold focus:ring-gold focus:ring-offset-0 bg-transparent">
                                            <span class="text-16px text-gray-500 mx-3 font-medium">مجانية</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" value="has_cert" class="filter-type-checkbox form-checkbox w-5 h-5 border-gray-400 rounded-sm text-gold focus:ring-gold focus:ring-offset-0 bg-transparent">
                                            <span class="text-16px text-gray-500 mx-3 font-medium">شهادة معتمدة</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="pt-2 text-center flex items-center gap-2 flex-col">
                                    <button type="button" id="apply-filter-btn"
                                        class="w-full bg-gold hover:bg-gold/90 text-white font-medium text-16px py-3 rounded-4px transition-colors">
                                        تصفية
                                    </button>
                                    <button type="button" id="reset-filter-btn"
                                        class="w-full bg-transparent hover:bg-gray-50 text-black font-medium text-16px py-3 rounded-4px transition-colors mt-2">
                                        الغاء التصفية
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 relative min-h-[300px]">
                    <!-- Premium Glassmorphic Loader Overlay -->
                    <div id="courses-loader" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-150">
                        <div class="flex flex-col items-center gap-3">
                            <span class="loading loading-spinner text-gold size-10"></span>
                        </div>
                    </div>

                    <div id="courses-container">
                        @include('landing_v1.pages.courses_list', ['courses' => $courses])
                    </div>
                </div>


        </section>


        <section class="section-gap ">
            <div class="container  ">
                <div class="bg-gold rounded-8px py-16 center flex-col text-center space-y-5">
                    <h2 class="font-bold text-56px text-white">حلول تعليمية مخصصة لكل احتياج</h2>
                    <p class="font-medium text-22px text-white">من التعلم الفردي إلى التطوير المؤسسي.. نحن شريكك في النجاح
                    </p>
                    <a href="{{ route('landing.v1.register') }}"
                        class="btn btn-primary h-13 rounded-2px px-11 text-white font-medium text-14px">
                        سجل الآن
                        <span class="icon icon-[tabler--arrow-left]"></span>
                    </a>
                </div>
            </div>

        </section>

    </main>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentCategoryId = '';
    let currentSort = 'latest';
    let currentPage = @json($courses instanceof \Illuminate\Pagination\AbstractPaginator ? $courses->currentPage() : 1);
    let fetchController = null;
    
    const loader = document.getElementById('courses-loader');
    const container = document.getElementById('courses-container');
    const baseUrl = @json(route('landing.v1.courses'));
    
    // Category Tabs click handler
    document.querySelectorAll('.category-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-tab-btn').forEach(b => {
                b.classList.remove('active', 'bg-primary', 'text-white');
                b.classList.add('text-primary');
            });
            
            this.classList.add('active', 'bg-primary', 'text-white');
            this.classList.remove('text-primary');
            
            currentCategoryId = this.getAttribute('data-category-id');
            currentPage = 1;
            fetchCourses();
        });
    });
    
    // Sort Button click handler
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sort-btn').forEach(b => {
                b.classList.remove('bg-primary', 'text-white');
                b.classList.add('bg-[#EBEBEB]', 'text-black');
            });
            
            this.classList.remove('bg-[#EBEBEB]', 'text-black');
            this.classList.add('bg-primary', 'text-white');
            
            currentSort = this.getAttribute('data-sort-val');
            currentPage = 1;
            fetchCourses();
        });
    });
    
    // Apply Filter Button
    const applyBtn = document.getElementById('apply-filter-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            currentPage = 1;
            fetchCourses();
            const dropdown = document.getElementById('dropdown-default');
            if (dropdown) {
                dropdown.click();
            }
        });
    }
    
    // Reset Filter Button
    const resetBtn = document.getElementById('reset-filter-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            document.querySelectorAll('.filter-type-checkbox').forEach(cb => {
                cb.checked = false;
            });
            currentPage = 1;
            fetchCourses();
            const dropdown = document.getElementById('dropdown-default');
            if (dropdown) {
                dropdown.click();
            }
        });
    }

    container.addEventListener('click', function (e) {
        const link = e.target.closest('a.landing-pagination__link');
        if (!link) {
            return;
        }
        e.preventDefault();
        const page = new URL(link.href, window.location.origin).searchParams.get('page');
        if (page) {
            currentPage = parseInt(page, 10);
            fetchCourses();
            container.closest('section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
    
    function fetchCourses() {
        if (fetchController) {
            fetchController.abort();
        }
        fetchController = new AbortController();

        loader.classList.remove('opacity-0', 'pointer-events-none');
        
        const types = [];
        document.querySelectorAll('.filter-type-checkbox:checked').forEach(cb => {
            types.push(cb.value);
        });
        
        const params = new URLSearchParams();
        if (currentCategoryId) {
            params.append('category_id', currentCategoryId);
        }
        if (currentSort) {
            params.append('sort', currentSort);
        }
        types.forEach(t => {
            params.append('types[]', t);
        });
        if (currentPage > 1) {
            params.set('page', String(currentPage));
        }
        
        fetch(baseUrl + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: fetchController.signal,
        })
        .then(response => response.json())
        .then(data => {
            container.innerHTML = data.html;
            history.replaceState(null, '', baseUrl + '?' + params.toString());
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Error fetching courses:', err);
            }
        })
        .finally(() => {
            loader.classList.add('opacity-0', 'pointer-events-none');
            fetchController = null;
        });
    }
});
</script>
@endpush
