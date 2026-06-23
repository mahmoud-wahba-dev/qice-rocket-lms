
@php($landingImg = asset('assets/landing_v1/img'))


<div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
    <div class="h-60 overflow-hidden rounded-t-[19px]">
        <img class="h-full w-full object-cover"
            src="{{ $landingImg }}/home/instructor.webp" alt="د. محمد أبو هيشة">
    </div>
    <div class="p-6">
        <h6 class="font-semibold text-24px text-blue mb-3">د. محمد أبو هيشة</h6>
        <ul class="list-inside list-disc font-normal text-14px text-blue space-y-1">
            <li>دكتوراه في الإدارة والتخطيط الصحي.</li>
            <li>استشاري الإدارة الصحية والمستشفيات.</li>
            <li>عمل مديرًا عامًا لعدة إدارات عامة بوزارة الصحة لمدة 30 سنة.</li>
        </ul>
    </div>
</div>