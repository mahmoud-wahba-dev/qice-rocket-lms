{{--
  Rich text field for HTML course descriptions (WYSIWYG).
  @param string $name
  @param string $value
  @param string $placeholder
  @param string $id
--}}
@php
    $name = $name ?? 'description';
    $value = (string) ($value ?? '');
    $placeholder = $placeholder ?? 'اكتب الوصف هنا...';
    $editorId = $id ?? ('rich-' . preg_replace('/[^a-z0-9_-]/i', '-', $name) . '-' . uniqid());
@endphp
<div class="rounded-12px border border-d9 overflow-hidden bg-white" data-rich-editor>
    <div class="flex flex-wrap items-center gap-1 px-2.5 py-2 border-b border-d9 bg-[#FAFAF4]" data-rich-toolbar role="toolbar" aria-label="أدوات التنسيق">
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="bold" title="عريض">
            <span class="icon-[tabler--bold] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="italic" title="مائل">
            <span class="icon-[tabler--italic] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="underline" title="تسطير">
            <span class="icon-[tabler--underline] size-4"></span>
        </button>
        <span class="w-px h-5 bg-d9 mx-1"></span>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="insertUnorderedList" title="قائمة">
            <span class="icon-[tabler--list] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="insertOrderedList" title="قائمة مرقمة">
            <span class="icon-[tabler--list-numbers] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="formatBlock" data-rich-value="h3" title="عنوان">
            <span class="icon-[tabler--h-3] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="formatBlock" data-rich-value="p" title="فقرة">
            <span class="icon-[tabler--pilcrow] size-4"></span>
        </button>
        <span class="w-px h-5 bg-d9 mx-1"></span>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="createLink" title="رابط">
            <span class="icon-[tabler--link] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary" data-rich-cmd="removeFormat" title="إزالة التنسيق">
            <span class="icon-[tabler--clear-formatting] size-4"></span>
        </button>
        <button type="button" class="size-8 rounded-8px center hover:bg-white border border-transparent hover:border-d9 transition text-primary ms-auto" data-rich-toggle-source title="عرض المصدر">
            <span class="icon-[tabler--code] size-4"></span>
        </button>
    </div>

    <div id="{{ $editorId }}"
        class="min-h-48 max-h-[28rem] overflow-y-auto px-4 py-4 font-medium text-15px sm:text-16px text-black text-start leading-relaxed focus:outline-none prose-sm rich-editor-content"
        contenteditable="true"
        data-rich-content
        data-placeholder="{{ $placeholder }}"
        role="textbox"
        aria-multiline="true"
        aria-label="{{ $placeholder }}">{!! $value !!}</div>

    <textarea name="{{ $name }}" id="{{ $editorId }}-source" class="hidden w-full min-h-48 max-h-[28rem] overflow-y-auto px-4 py-4 font-mono text-13px text-black border-0 focus:outline-none resize-y"
        data-rich-source
        dir="ltr">{{ $value }}</textarea>
</div>
