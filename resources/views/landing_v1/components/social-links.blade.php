@php
    $socialLinks = collect(config('landing_v1.social_links', []))
        ->filter(fn ($item) => !empty($item['url'] ?? null));
@endphp

@if ($socialLinks->isNotEmpty())
    <div class="mt-6 flex flex-wrap items-center gap-3">
        @foreach ($socialLinks as $social)
            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                aria-label="{{ $social['label'] ?? 'Social' }}"
                class="flex size-10 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white transition-all duration-200 hover:border-gold hover:bg-white/20 hover:text-gold">
                @switch($social['icon'] ?? '')
                    @case('telegram')
                        <span class="icon-[tabler--brand-telegram] size-5"></span>
                        @break
                    @case('whatsapp')
                        <span class="icon-[tabler--brand-whatsapp] size-5"></span>
                        @break
                    @case('instagram')
                        <span class="icon-[tabler--brand-instagram] size-5"></span>
                        @break
                    @case('linkedin')
                        <span class="icon-[tabler--brand-linkedin] size-5"></span>
                        @break
                    @case('x')
                        <span class="icon-[tabler--brand-x] size-5"></span>
                        @break
                    @default
                        <span class="icon-[tabler--link] size-5"></span>
                @endswitch
            </a>
        @endforeach
    </div>
@endif
