@props([
    'image',
    'title',
    'breadcrumbs' => [],
    'minHeightClass' => 'min-h-[58vh]',
    'titleClass' => 'text-48px',
])

<header class="{{ $minHeightClass }} text-white flex items-center"
    style="background-image: url('{{ $image }}'); background-repeat: no-repeat; background-size: cover;">
    <div class="container">
        <div class="lg:w-[50%] w-full px-4">
            @if (!empty($breadcrumbs))
                <div class="breadcrumbs mb-1.5">
                    <ul>
                        @foreach ($breadcrumbs as $crumb)
                            <li>
                                <a href="{{ $crumb['href'] ?? '#' }}" class="font-medium text-24px text-white">
                                    {{ $crumb['label'] ?? '' }}
                                </a>
                            </li>
                            @if (!$loop->last)
                                <li class="breadcrumbs-separator rtl:rotate-180">
                                    <span class="icon-[tabler--chevron-right] text-white"></span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            <h1 class="font-bold {{ $titleClass }} mb-5 text-white">{{ $title }}</h1>
            {{ $slot }}
        </div>
    </div>
</header>
