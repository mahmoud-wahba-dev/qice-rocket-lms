@php
    $courseSlug = $slug ?? 'demo';
@endphp

<div class="dropdown relative inline-flex rtl:[--placement:bottom]">
    <button id="course-tools-toggle" type="button"
        class="dropdown-toggle inline-flex items-center gap-2 font-semibold text-14px sm:text-15px text-white hover:bg-white/10 rounded-10px px-2 sm:px-3 py-2 transition"
        aria-haspopup="menu" aria-expanded="false">
        <span class="icon-[tabler--pencil] size-5 shrink-0"></span>
        <span class="max-sm:hidden">أدوات الدورة</span>
        <span class="icon-[tabler--chevron-down] size-4 opacity-80 dropdown-open:rotate-180 transition-transform duration-200"></span>
    </button>

    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-64 py-2 rounded-14px border border-d9 shadow-xl bg-white z-[60]"
        role="menu" aria-orientation="vertical" aria-labelledby="course-tools-toggle">
        <li>
            <a href="#" class="dropdown-item flex items-center gap-3 px-4 py-3 font-medium text-15px text-gray hover:bg-fa transition">
                <span class="icon-[tabler--external-link] size-6 text-gray shrink-0"></span>
                صفحة الدورة
            </a>
        </li>
        <li>
            <a href="{{ route('panel.v1.student.course.forum', ['slug' => $courseSlug]) }}"
                class="dropdown-item flex items-center gap-3 px-4 py-3 font-medium text-15px text-gray hover:bg-fa transition">
                <span class="icon-[tabler--bell-plus] size-6 text-gray shrink-0"></span>

                منتدى الدورة
            </a>
        </li>
        <li>
            <a href="#" class="dropdown-item flex items-center gap-3 px-4 py-3 font-medium text-15px text-gray hover:bg-fa transition">
                <span class="icon-[tabler--user] size-6 text-gray shrink-0"></span>
                ملف المدرب
            </a>
        </li>
        <li>
            <a href="#" class="dropdown-item flex items-center gap-3 px-4 py-3 font-medium text-15px text-gray hover:bg-fa transition">
                <span class="icon-[tabler--bell-plus] size-6 text-gray shrink-0"></span>
                إضافة تاريخ تذكير
            </a>
        </li>
        <li>
            <button type="button"
                class="dropdown-item flex items-center gap-3 px-4 py-3 font-medium text-15px text-gray hover:bg-fa transition w-full text-start"
                aria-haspopup="dialog" data-overlay="#course-support-modal">
                <span class="icon-[tabler--help-circle] size-6 text-gray shrink-0"></span>
                احصل على مساعدة
            </button>
        </li>
    </ul>
</div>
