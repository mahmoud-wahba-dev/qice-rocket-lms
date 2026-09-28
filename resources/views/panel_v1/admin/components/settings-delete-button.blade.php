@php
    $btnClass = $btnClass ?? 'inline-flex items-center gap-2 font-semibold text-14px text-[#DC2626] hover:opacity-80';
    $btnText = $btnText ?? trans('admin/main.delete');
    $confirm = $confirm ?? 'هل أنت متأكد من الحذف؟';
@endphp
<form method="POST" action="{{ $url }}" class="inline" onsubmit="return confirm(@json($confirm));">
    @csrf
    <button type="submit" class="{{ $btnClass }}">
        <span class="icon-[tabler--trash] size-4"></span>
        {{ $btnText }}
    </button>
</form>
