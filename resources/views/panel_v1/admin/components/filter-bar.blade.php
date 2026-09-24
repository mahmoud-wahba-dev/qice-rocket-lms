<form method="GET" action="{{ url()->current() }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 mb-5 sm:mb-6">
    {{-- الحفاظ على فلاتر القوائم المنسدلة عند البحث من الشريط العلوي --}}
    @foreach (request()->except(['search', 'page']) as $fkey => $fval)
        @if(!is_array($fval))
            <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
        @endif
    @endforeach
    <div class="relative flex-1 min-w-[14rem]">
        <span class="icon-[tabler--search] size-4 absolute start-3.5 top-1/2 -translate-y-1/2 text-gray"></span>
        <input type="search" name="search" value="{{ request('search') }}"
            class="input input-bordered w-full h-12 rounded-12px border-d9 font-medium text-14px text-black focus:outline-none focus:border-primary !ps-10"
            placeholder="ابحث بالرقم أو الاسم أو غير ذلك...">
    </div>
    <button type="submit"
        class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-semibold text-14px hover:opacity-95 transition shrink-0">
        <span class="icon-[tabler--search] size-4"></span>
        بحث
    </button>
    @if(request('search'))
        @php
            $clearQuery = request()->except(['search', 'page']);
            $clearUrl = url()->current() . (count($clearQuery) ? '?' . http_build_query($clearQuery) : '');
        @endphp
        <a href="{{ $clearUrl }}" class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-medium text-14px text-primary hover:bg-[#FAFAF4] transition">
            مسح
        </a>
    @endif
</form>
