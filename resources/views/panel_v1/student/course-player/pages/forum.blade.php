@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
    $forumData = $forum ?? ['banner_title' => 'منتدى الدورة', 'posts' => []];
@endphp

<div class="flex flex-col gap-8 pb-8">
    <div class="relative overflow-hidden rounded-20px bg-primary min-h-36 sm:min-h-44 center px-6 py-10">
        <div class="pointer-events-none absolute inset-0 opacity-30" aria-hidden="true">
            <div class="absolute -top-16 -start-10 size-56 rounded-full bg-[#0FC787] blur-[80px]"></div>
            <div class="absolute -bottom-20 -end-10 size-56 rounded-full bg-white/20 blur-[70px]"></div>
        </div>
        <h1 class="relative font-extrabold text-24px sm:text-32px text-white text-center leading-snug">
            {{ $forumData['banner_title'] }}
        </h1>
    </div>

    <div class="border border-d9 rounded-20px bg-white px-5 sm:px-6 py-5">
        <textarea rows="4"
            class="textarea textarea-bordered w-full border-0 bg-transparent font-medium text-15px text-black focus:outline-none min-h-28 resize-y p-0"
            placeholder="اشرح الفكرة أو السؤال بتفصيل يساعد الآخرين على التفاعل معك"></textarea>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-d9 mt-2">
            <div class="flex flex-wrap items-center gap-4">
                <button type="button" class="inline-flex items-center gap-2 font-medium text-13px text-gray hover:text-primary transition">
                    <span class="icon-[tabler--at] size-5"></span>
                    إشارة إلى عضو
                </button>
                <button type="button" class="inline-flex items-center gap-2 font-medium text-13px text-gray hover:text-primary transition">
                    <span class="icon-[tabler--link] size-5"></span>
                    إرفاق رابط
                </button>
                <button type="button" class="inline-flex items-center gap-2 font-medium text-13px text-gray hover:text-primary transition">
                    <span class="icon-[tabler--paperclip] size-5"></span>
                    إرفاق ملف
                </button>
            </div>
            <button type="button" class="btn btn-primary rounded-10px h-11 px-5 font-bold text-14px gap-2">
                <span class="icon-[tabler--send] size-5"></span>
                أرسل رسالتك
            </button>
        </div>
    </div>

    <div class="flex flex-col gap-5">
        @foreach ($forumData['posts'] as $post)
            <article class="border border-d9 rounded-20px bg-white px-5 sm:px-6 py-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-11 rounded-full bg-primary center shrink-0">
                        <span class="font-bold text-16px text-white">{{ $post['initial'] }}</span>
                    </div>
                    <div>
                        <p class="font-bold text-16px text-primary">{{ $post['author'] }}</p>
                        <p class="font-medium text-13px text-gray">{{ $post['time'] }}</p>
                    </div>
                </div>

                <p class="font-medium text-15px text-black leading-relaxed mb-5">{{ $post['body'] }}</p>

                <div class="flex flex-wrap items-center gap-5 text-gray">
                    <button type="button" class="inline-flex items-center gap-1.5 font-medium text-14px hover:text-primary transition">
                        <span class="icon-[tabler--heart] size-5"></span>
                        {{ $post['likes'] }}
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 font-medium text-14px hover:text-primary transition">
                        <span class="icon-[tabler--message-circle] size-5"></span>
                        {{ $post['comments'] }}
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 font-medium text-14px hover:text-primary transition">
                        <span class="icon-[tabler--share] size-5"></span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 font-medium text-14px hover:text-primary transition ms-auto">
                        <span class="icon-[tabler--bookmark] size-5"></span>
                    </button>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
