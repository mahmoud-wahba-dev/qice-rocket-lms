@extends('landing_v1.layouts.app')
@php($landingImg = asset('assets/landing_v1/img'))

@section('content')
    <main>

        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="relative mb-25 h-[600px] rounded-10px overflow-hidden" >
                    <img src="{{ $post->image ?? ($landingImg . '/home/hero-bg-opt.webp') }}" alt="{{ $post->title }}"
                        class="size-full object-cover rounded-10px" width="1200" height="600" loading="eager"
                        decoding="async">
                    <div class="absolute top-0 left-0 w-full h-full bg-linear-blog-details rounded-10px"></div>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-14px text-primary/70 mb-6">
                    @if (!empty($post->category))
                        <span>{{ $post->category->title }}</span>
                    @endif
                    @if (!empty($post->created_at))
                        <span>{{ dateTimeFormat($post->created_at, 'j M Y') }}</span>
                    @endif
                    @if (!empty($post->author))
                        <span>{{ $post->author->full_name }}</span>
                    @endif
                </div>

                <h1 class="font-bold text-30px xl:text-48px text-primary mb-6">{{ $post->title }}</h1>

                @if (!empty($post->subtitle))
                    <p class="font-medium text-20px text-primary/80 mb-8">{{ $post->subtitle }}</p>
                @endif

                <div class="leading-relaxed prose prose-lg max-w-none mb-16">
                    {!! $post->content !!}
                </div>

                @if (!empty($recentPosts) && $recentPosts->isNotEmpty())
                    <h2 class="font-bold text-32px text-primary mb-8">مقالات ذات صلة</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($recentPosts as $recentPost)
                            <x-landing_v1::blog-card
                                :title="$recentPost->title"
                                :image="$recentPost->image ?? asset('assets/landing_v1/img/home/news1-opt.webp')"
                                :slug="$recentPost->slug"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
