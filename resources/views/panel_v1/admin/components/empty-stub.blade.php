@props([
    'title' => 'قريباً',
    'subtitle' => 'سيتم إضافة محتوى هذا القسم لاحقاً.',
])

<div class="border border-d9 rounded-14px bg-white px-6 py-16 sm:py-20 text-center">
    <span class="size-14 rounded-14px bg-primary/10 center mx-auto mb-4">
        <span class="icon-[tabler--layout] size-7 text-primary"></span>
    </span>
    <h2 class="font-semibold text-22px sm:text-24px text-primary mb-2">{{ $title }}</h2>
    <p class="font-medium text-15px sm:text-16px text-gray">{{ $subtitle }}</p>
</div>
