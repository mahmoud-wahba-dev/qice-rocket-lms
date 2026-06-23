@props([])
@php($landingImg = asset('assets/landing_v1/img'))

<div class="bg-white  rounded-19px  border border-[#E0D4BC]">
    <div class="h-60 mb-6 overflow-hidden">
        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]"
            src="{{ $landingImg }}/home/course.webp" alt="course 1">
    </div>

    <div class="p-6">
        <h6 class="font-semibold text-20px text-primary mb-2">التسويق الرقمي</h6>
        <p class="font-normal text-12px text-7a mb-2">Digital Marketing</p>
        <p class="font-normal text-13px text-7a mb-3">
            دورة تطويرية
        </p>
        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
            <div class="avatar">

                <div class="size-10 rounded-full">
                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar" />
                </div>
            </div>

            <p class="font-medium text-77 text-base">اسم المعلم</p>
        </div>

        <div class="flex items-center justify-between">
            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                699 ريال
            </span>
            <a href="" class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">سجّل
                الآن</a>
        </div>
    </div>
</div>
