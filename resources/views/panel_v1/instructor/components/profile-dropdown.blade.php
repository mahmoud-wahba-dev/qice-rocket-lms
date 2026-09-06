@php
    $name = $authUser->full_name ?? ($instructorName ?? 'مدرب');
    $email = $authUser->email ?? ($instructorEmail ?? '');
    $initial = mb_substr($name, 0, 1);
    $onDark = !empty($profileOnDark);
@endphp

<div class="dropdown relative inline-flex [--auto-close:inside] rtl:[--placement:bottom-end]">
    <button id="instructor-user-toggle" type="button"
        class="dropdown-toggle inline-flex items-center gap-2 rounded-10px px-1.5 py-1.5 transition {{ $onDark ? 'hover:bg-white/10' : 'hover:bg-primary/5' }}"
        aria-haspopup="menu" aria-expanded="false" aria-label="قائمة المدرب">
        <div class="size-9 rounded-full overflow-hidden {{ $onDark ? 'bg-white' : 'bg-primary' }} center shrink-0">
            <img src="{{ $authUser->getAvatar() }}"
                 alt="{{ $name }}"
                 class="w-full h-full object-cover"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            <span class="hidden w-full h-full items-center justify-center font-bold text-16px {{ $onDark ? 'text-primary bg-white' : 'text-white bg-primary' }}">
                {{ $initial }}
            </span>
        </div>
        <span class="max-md:hidden font-semibold text-14px {{ $onDark ? 'text-white' : 'text-primary' }}">{{ $name }}</span>
        <span class="icon-[tabler--chevron-down] size-4 max-md:hidden dropdown-open:rotate-180 transition-transform {{ $onDark ? 'text-white/70' : 'text-primary/60' }}"></span>
    </button>

    <div class="dropdown-menu dropdown-open:opacity-100 hidden w-72 p-3 rounded-16px border border-d9 shadow-[0_12px_40px_rgba(15,76,69,0.12)] bg-white z-50"
        role="menu" aria-labelledby="instructor-user-toggle">
        <div class="flex items-center gap-3 px-2 py-3 mb-2 border-b border-d9">
            <div class="size-12 rounded-full overflow-hidden bg-primary center shrink-0">
                <img src="{{ $authUser->getAvatar() }}" alt=""
                     class="w-full h-full object-cover"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <span class="hidden w-full h-full items-center justify-center font-bold text-18px text-white bg-primary">
                    {{ $initial }}
                </span>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-15px text-primary truncate">{{ $name }}</p>
                <p class="font-medium text-12px text-gray truncate">{{ $email }}</p>
                <span class="inline-flex items-center gap-1 mt-1 rounded-full bg-[#0FC787]/15 px-2 py-0.5 font-semibold text-11px text-[#0FC787]">
                    <span class="icon-[tabler--rosette-discount-check] size-3.5"></span>
                    مدرب موثق
                </span>
            </div>
        </div>

        <ul class="py-1">
            <li>
                <a href="{{ route('panel.v1.instructor.home') }}"
                    class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">
                    لوحة التحكم
                </a>
            </li>
            <li>
                <a href="#" class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">الملف الشخصي</a>
            </li>
            <li>
                <a href="#" class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">الأرباح</a>
            </li>
            <li>
                <a href="#" class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">الإشعارات</a>
            </li>
            <li>
                <a href="#" class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">المفضلة</a>
            </li>
            <li>
                <a href="#" class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">الإعدادات</a>
            </li>
            <li>
                <a href="#" class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-primary hover:bg-fa transition">الدعم</a>
            </li>
            <li>
                <a href="/logout"
                    class="dropdown-item rounded-10px px-4 py-2.5 font-semibold text-14px text-[#E11D48] hover:bg-red-50 transition">
                    تسجيل الخروج
                </a>
            </li>
        </ul>
    </div>
</div>
