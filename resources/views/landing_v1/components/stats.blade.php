@props([
    'titleClass' => '',
])

<section class="section-gap">
    <div class="container">
        <h2 class="font-semibold text-32px text-primary mb-8 {{ $titleClass }}">سمات في ارقام</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
            <div class="center flex-col px-4">
                <p class="font-bold flex items-center flex-nowrap text-71px max-xl:text-60px max-md:text-48px mb-7 text-secondary">
                    <span>+</span>
                    <span data-toggle-count='{"target":"#stats-1","min":0,"max":95000,"duration":1200}'>
                        0
                    </span>
                </p>
                <span id="stats-1" class="sr-only">95000</span>
                <p class="font-semibold text-28px max-xl:text-24px text-primary">عميل مستفيد </p>

            </div>

            <div class="center flex-col px-8 border-x border-[#B9A68566]">
                <p class="font-bold flex items-center flex-nowrap text-71px max-xl:text-60px max-md:text-48px mb-7 text-secondary">
                    <span>+</span>
                    <span data-toggle-count='{"target":"#stats-2","min":0,"max":620,"duration":1200}'>
                        0
                    </span>
                </p>
                <span id="stats-2" class="sr-only">620</span>
                <p class="font-semibold text-28px max-xl:text-24px text-primary">برنامج تدريبي </p>

            </div>

            <div class="center flex-col px-8 border-e border-[#B9A68566]">
                <p class="font-bold flex items-center flex-nowrap text-71px max-xl:text-60px max-md:text-48px mb-7 text-secondary">
                    <span>+</span>
                    <span data-toggle-count='{"target":"#stats-3","min":0,"max":66,"duration":1200}'>
                        0
                    </span>
                </p>
                <span id="stats-3" class="sr-only"> 66</span>
                <p class="font-semibold text-28px max-xl:text-24px text-primary">مستشارين ومدربين </p>

            </div>

            <div class="center flex-col px-4">
                <p class="font-bold flex items-center flex-nowrap text-71px max-xl:text-60px max-md:text-48px mb-7 text-secondary">
                    <span>+</span>
                    <span data-toggle-count='{"target":"#stats-4","min":0,"max":1200,"duration":1200}'>
                        0
                    </span>
                </p>
                <span id="stats-4" class="sr-only">+ 1200</span>
                <p class="font-semibold text-28px max-xl:text-24px text-primary">خدمة استشارية </p>

            </div>
        </div>
    </div>
</section>
