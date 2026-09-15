{{--
  Shared panel_v1 file upload + preview (client-side before DB save).
  Usage:
    @include('panel_v1.components.file-upload', [
        'name' => 'avatar',
        'accept' => 'image/*',
        'label' => 'اختر صورة من جهازك',
        'hint' => 'PNG أو JPG',
        'media' => true, // large image/video preview
        'valueUrl' => $url, // or existing => [panelV1UserMediaPreview(...)]
    ])
--}}
@php
    $fieldName = $name ?? 'upload';
    $accept = $accept ?? 'image/*,.pdf,.doc,.docx,.zip';
    $label = $label ?? 'اضغط هنا لرفع ملف من جهازك';
    $hint = $hint ?? 'يدعم الصور وملفات PDF والمستندات';
    $required = !empty($required);
    $multiple = !empty($multiple);
    $media = !empty($media);
    $disabled = !empty($disabled);
    $inputClass = $inputClass ?? '';
    $compact = !empty($compact);
    $uid = $id ?? ('v1fu-' . str_replace('.', '', uniqid('', true)));
    $existingLabel = $existingLabel ?? 'الملف الحالي';

    $existingItems = collect($existing ?? [])->filter()->values()->all();
    if (!empty($valueUrl)) {
        $existingItems[] = function_exists('panelV1FilePreviewItem')
            ? panelV1FilePreviewItem($valueUrl, $valueName ?? null, $valueSize ?? null, $valueMime ?? null)
            : ['name' => $valueName ?? 'ملف مرفوع', 'url' => $valueUrl, 'kind' => 'file', 'size' => $valueSize ?? ''];
    }
@endphp

<div class="v1-file-upload w-full {{ $class ?? '' }}"
    data-v1-file-upload
    data-v1-multiple="{{ $multiple ? '1' : '0' }}"
    data-v1-media="{{ $media ? '1' : '0' }}"
    data-v1-accept="{{ $accept }}">

    @if (count($existingItems))
        <div class="space-y-3 mb-4" data-v1-existing>
            <p class="font-semibold text-13px text-gray text-start">{{ $existingLabel }}</p>
            @foreach ($existingItems as $item)
                @include('panel_v1.components.file-preview-card', [
                    'item' => $item,
                    'removable' => false,
                    'media' => $media,
                ])
            @endforeach
        </div>
    @endif

    <div class="space-y-3 mb-4 hidden" data-v1-preview></div>

    <label for="{{ $uid }}"
        class="group flex {{ $compact ? 'flex-row items-center gap-3 px-4 py-3' : 'flex-col items-center justify-center gap-3 px-5 py-8' }}
            w-full rounded-14px border border-dashed border-primary/35 bg-[#ECFDF5]/70
            {{ $disabled ? 'opacity-50 pointer-events-none cursor-not-allowed' : 'cursor-pointer hover:bg-[#D1FAE5]/50 hover:border-primary/60' }}
            transition text-center"
        data-v1-dropzone>
        <span class="size-11 rounded-full bg-white border border-primary/15 center shrink-0 shadow-sm">
            <span class="icon-[tabler--cloud-upload] size-5 text-primary"></span>
        </span>
        <span class="min-w-0">
            <span class="block font-bold text-14px sm:text-15px text-primary leading-snug" data-v1-label>
                {{ $label }}
            </span>
            @if ($hint !== '')
                <span class="block font-medium text-12px text-gray mt-1" data-v1-hint>{{ $hint }}</span>
            @endif
            <span class="hidden font-medium text-12px text-primary mt-1" data-v1-selected-name></span>
        </span>
        <input
            id="{{ $uid }}"
            type="file"
            name="{{ $fieldName }}{{ $multiple ? '[]' : '' }}"
            class="sr-only {{ $inputClass }}"
            accept="{{ $accept }}"
            @if ($required) required @endif
            @if ($multiple) multiple @endif
            @if ($disabled) disabled @endif
            data-v1-file-input
        >
    </label>

    <p class="hidden mt-2 font-medium text-13px text-[#EF4444] text-start" data-v1-error role="alert"></p>
</div>
