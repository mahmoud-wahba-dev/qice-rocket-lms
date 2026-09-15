@php
    $item = $item ?? [];
    $name = $item['name'] ?? 'ملف';
    $url = $item['url'] ?? null;
    $kind = $item['kind'] ?? (function_exists('panelV1FileKind') ? panelV1FileKind($url ?? $name) : 'file');
    $size = $item['size'] ?? '';
    $removable = !empty($removable);
    $media = !empty($media);
    $isImage = $kind === 'image' && !empty($url);
    $isVideo = $kind === 'video' && !empty($url);
    $isLocalPreview = !empty($item['local']);
@endphp

<div class="rounded-14px border border-d9 bg-white overflow-hidden text-start"
    data-v1-preview-card
    @if ($isLocalPreview) data-v1-local-preview @endif>

    @if ($media && ($isImage || $isVideo))
        <div class="w-full bg-fa center overflow-hidden {{ $media ? 'aspect-[16/10] max-h-56' : '' }}">
            @if ($isImage)
                <img src="{{ $url }}" alt="{{ $name }}" class="size-full object-cover" data-v1-thumb>
            @else
                <video src="{{ $url }}" class="size-full object-cover" controls muted playsinline data-v1-thumb></video>
            @endif
        </div>
        <div class="px-4 py-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="font-bold text-14px text-primary truncate" title="{{ $name }}">{{ $name }}</p>
                @if ($size !== '' && $size !== null)
                    <p class="font-medium text-12px text-gray mt-0.5">{{ $size }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0">
                @if (!empty($url) && empty($item['local']))
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="font-bold text-13px text-primary hover:underline">عرض</a>
                @endif
                @if ($removable)
                    <button type="button" class="font-bold text-13px text-[#EF4444]" data-v1-remove-preview>إزالة</button>
                @endif
            </div>
        </div>
    @else
        <div class="flex items-stretch gap-3">
            <div class="w-20 sm:w-24 shrink-0 bg-fa center overflow-hidden border-e border-d9">
                @if ($isImage)
                    <img src="{{ $url }}" alt="{{ $name }}" class="size-full object-cover min-h-20" data-v1-thumb>
                @elseif ($kind === 'pdf')
                    <span class="icon-[tabler--file-type-pdf] size-8 text-[#EF4444]"></span>
                @elseif ($kind === 'video')
                    <span class="icon-[tabler--video] size-8 text-primary"></span>
                @elseif ($kind === 'doc')
                    <span class="icon-[tabler--file-text] size-8 text-[#2563EB]"></span>
                @else
                    <span class="icon-[tabler--file] size-8 text-gray"></span>
                @endif
            </div>

            <div class="flex-1 min-w-0 py-3 pe-2">
                <p class="font-bold text-14px sm:text-15px text-primary truncate" title="{{ $name }}">{{ $name }}</p>
                <p class="font-medium text-12px text-gray mt-0.5">
                    @if ($size !== '' && $size !== null)
                        {{ $size }}
                        <span class="mx-1 text-d9">•</span>
                    @endif
                    {{ match ($kind) {
                        'image' => 'صورة',
                        'pdf' => 'PDF',
                        'video' => 'فيديو',
                        'doc' => 'مستند',
                        default => 'ملف',
                    } }}
                </p>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    @if (!empty($url) && empty($item['local']))
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                            class="font-bold text-13px text-primary hover:underline">
                            {{ $isImage ? 'عرض' : 'فتح / تنزيل' }}
                        </a>
                    @endif
                    @if ($removable)
                        <button type="button" class="font-bold text-13px text-[#EF4444]" data-v1-remove-preview>
                            إزالة
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
