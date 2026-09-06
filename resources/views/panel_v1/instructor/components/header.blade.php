<nav class="navbar min-h-14 py-0 px-2 sm:px-4 bg-white items-center">
    <div class="navbar-start gap-2 !flex !items-center">
        <button type="button"
            class="btn btn-soft btn-square btn-sm lg:hidden text-primary !inline-flex !items-center !justify-center"
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-controls="instructor-layout-toggle"
            data-overlay="#instructor-layout-toggle">
            <span class="icon-[tabler--menu-2] size-5"></span>
        </button>
    </div>

    {{-- RTL: navbar-end sits on the left; last child = farthest left (profile) --}}
    <div class="navbar-end gap-0.5 sm:gap-1 !flex !items-center">
        <button type="button"
            class="btn btn-text btn-square btn-sm text-primary !inline-flex !items-center !justify-center"
            aria-label="بحث">
            <span class="icon-[tabler--search] size-5"></span>
        </button>
        <button type="button"
            class="btn btn-text btn-square btn-sm text-primary !inline-flex !items-center !justify-center"
            aria-label="السلة">
            <span class="icon-[tabler--shopping-bag] size-5"></span>
        </button>
        <button type="button"
            class="btn btn-text btn-square btn-sm text-primary !inline-flex !items-center !justify-center"
            aria-label="المفضلة">
            <span class="icon-[tabler--heart] size-5"></span>
        </button>
        <button type="button"
            class="btn btn-text btn-square btn-sm text-primary !inline-flex !items-center !justify-center"
            aria-label="الإشعارات">
            <span class="icon-[tabler--bell] size-5"></span>
        </button>

        @include('panel_v1.instructor.components.profile-dropdown')
    </div>
</nav>
