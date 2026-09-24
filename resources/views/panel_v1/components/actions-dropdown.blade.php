{{--
  Shared RTL actions dropdown (⋯) — panel_v1 admin + instructor tables.

  @param string $id Unique menu id
  @param array $items Each item:
    - label (string, required)
    - url (string, optional GET link)
    - action (string, optional POST form action)
    - method (string, default POST) — only for form items
    - confirm (string, optional onsubmit confirm)
    - tone: gray|primary|success|danger (default gray)
    - disabled (bool)
--}}
@php
    $menuId = $id ?? ('actions-menu-' . uniqid());
    $items = $items ?? [];
    $extraClass = $class ?? '';
    $toneClass = [
        'gray' => 'font-medium text-14px text-gray hover:bg-[#FAFAF4]',
        'primary' => 'font-medium text-14px text-primary hover:bg-[#FAFAF4]',
        'success' => 'font-semibold text-14px text-[#16A34A] hover:bg-[#F0FDF4]',
        'danger' => 'font-semibold text-14px text-[#DC2626] hover:bg-[#FEF2F2]',
    ];
@endphp

@if (!empty($items))
<div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end] {{ $extraClass }}">
    <button type="button"
        class="dropdown-toggle size-9 rounded-10px border border-d9 bg-white center hover:bg-[#FAFAF4] transition"
        aria-label="اجراءات" id="{{ $menuId }}">
        <span class="icon-[tabler--dots] size-4 text-gray"></span>
    </button>
    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-[13rem] py-1.5 rounded-12px border border-d9 bg-white shadow-xl z-40 text-start"
        role="menu" aria-labelledby="{{ $menuId }}">
        @foreach ($items as $item)
            @php
                $label = $item['label'] ?? '';
                $tone = $item['tone'] ?? 'gray';
                $cls = $toneClass[$tone] ?? $toneClass['gray'];
                $disabled = !empty($item['disabled']);
                if ($disabled) {
                    $cls .= ' opacity-40 pointer-events-none';
                }
            @endphp
            @if (!empty($item['url']))
                <li>
                    <a href="{{ $item['url'] }}"
                        class="dropdown-item px-4 py-2.5 {{ $cls }}"
                        @if (!empty($item['target'])) target="{{ $item['target'] }}" @endif
                        @if (($item['target'] ?? '') === '_blank') rel="noopener noreferrer" @endif>{{ $label }}</a>
                </li>
            @elseif (!empty($item['action']))
                <li>
                    <form method="POST" action="{{ $item['action'] }}"
                        @if (!empty($item['confirm'])) onsubmit="return confirm(@js($item['confirm']));" @endif>
                        @csrf
                        @if (!empty($item['method']) && strtoupper($item['method']) !== 'POST')
                            @method($item['method'])
                        @endif
                        <button type="submit" class="dropdown-item w-full text-start px-4 py-2.5 {{ $cls }}"
                            {{ $disabled ? 'disabled' : '' }}>{{ $label }}</button>
                    </form>
                </li>
            @endif
        @endforeach
    </ul>
</div>
@endif
