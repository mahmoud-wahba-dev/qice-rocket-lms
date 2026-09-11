@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">منتدياتي</h1>
            <p class="font-semibold text-20px text-gray">مواضيعك ومشاركاتك والعناصر المحفوظة</p>
        </div>

        <nav class="tabs tabs-bordered tabs-lg w-full md:w-[75%] overflow-x-auto mb-10" role="tablist">
            <button type="button" class="tab active font-bold text-16px" data-v1-tab="#forums-1" aria-selected="true">المواضيع</button>
            <button type="button" class="tab font-bold text-16px" data-v1-tab="#forums-2" aria-selected="false">مشاركاتي</button>
            <button type="button" class="tab font-bold text-16px" data-v1-tab="#forums-3" aria-selected="false">المحفوظات</button>
        </nav>

        <div id="forums-1" role="tabpanel">
            @forelse ($topics ?? [] as $topic)
                <div class="border border-d9 rounded-16px bg-white px-6 py-5 mb-4 flex items-start justify-between gap-4">
                    <div>
                        <p class="font-bold text-16px text-primary">{{ $topic->title }}</p>
                        <p class="font-medium text-13px text-gray">{{ $topic->creator->full_name ?? '' }} — {{ date('Y/m/d', (int) $topic->created_at) }}</p>
                        <p class="font-medium text-14px text-gray mt-2">{{ \Illuminate\Support\Str::limit(strip_tags($topic->description ?? ''), 160) }}</p>
                    </div>
                    <form method="POST" action="{{ route('panel.v1.student.forums.bookmark', ['topicId'=>$topic->id]) }}">
                        @csrf
                        <button type="submit" class="font-bold text-13px text-primary hover:underline">حفظ</button>
                    </form>
                </div>
            @empty
                <div class="border border-dashed border-d9 rounded-16px bg-fa/50 px-8 py-12 center flex-col text-center">
                    <p class="font-semibold text-16px text-gray">لا توجد مواضيع</p>
                </div>
            @endforelse
        </div>

        <div id="forums-2" class="hidden" role="tabpanel">
            @forelse ($posts ?? [] as $post)
                <div class="border border-d9 rounded-16px bg-white px-6 py-5 mb-4">
                    <p class="font-bold text-15px text-primary">{{ $post->topic->title ?? '' }}</p>
                    <p class="font-medium text-14px text-gray mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($post->description ?? $post->message ?? ''), 200) }}</p>
                    <p class="font-medium text-12px text-gray mt-2">{{ date('Y/m/d', (int) $post->created_at) }}</p>
                </div>
            @empty
                <div class="border border-dashed border-d9 rounded-16px bg-fa/50 px-8 py-12 center flex-col text-center">
                    <p class="font-semibold text-16px text-gray">لا توجد مشاركات</p>
                </div>
            @endforelse
        </div>

        <div id="forums-3" class="hidden" role="tabpanel">
            @forelse ($bookmarks ?? [] as $bm)
                <div class="border border-d9 rounded-16px bg-white px-6 py-5 mb-4">
                    <p class="font-bold text-15px text-primary">{{ $bm->topic->title ?? '' }}</p>
                    <p class="font-medium text-12px text-gray mt-1">{{ date('Y/m/d', (int) $bm->created_at) }}</p>
                </div>
            @empty
                <div class="border border-dashed border-d9 rounded-16px bg-fa/50 px-8 py-12 center flex-col text-center">
                    <p class="font-semibold text-16px text-gray">لا توجد محفوظات</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
