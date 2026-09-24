@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $card = $settingsCard ?? [];
    $items = $settingsItems ?? collect();
@endphp
<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? ($card['title'] ?? 'الإعدادات'),
        'subtitle' => $card['hint'] ?? '',
    ])
        @slot('actions')
            <a href="{{ $hubUrl ?? route('panel.v1.admin.system.section', ['section' => 'settings']) }}"
                class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة للإعدادات
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-d9 bg-white p-4 sm:p-5 shadow-sm flex items-start gap-4">
        <span class="size-14 rounded-12px bg-primary/10 center shrink-0">
            <span class="{{ $card['icon'] ?? 'icon-[tabler--settings]' }} size-7 text-primary"></span>
        </span>
        <div class="min-w-0 text-start">
            <h2 class="font-bold text-18px text-primary mb-1">{{ $card['title'] ?? '' }}</h2>
            <p class="font-medium text-14px text-gray">{{ $card['hint'] ?? '' }}</p>
        </div>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-14px border border-d9 bg-white px-6 py-16 text-center">
            <p class="font-semibold text-18px text-primary mb-2">لا توجد إعدادات في هذه المجموعة</p>
            <p class="font-medium text-14px text-gray">لم يتم العثور على سجلات إعدادات مرتبطة بهذا القسم.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($items as $setting)
                @php
                    $raw = (string) ($setting->value ?? '');
                    $pretty = $raw;
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                        $pretty = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    }
                    $isLong = mb_strlen($pretty) > 120;
                @endphp
                <div class="rounded-14px border border-d9 bg-white overflow-hidden shadow-sm">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between gap-3">
                        <div class="min-w-0 text-start">
                            <p class="font-bold text-15px text-primary truncate" dir="ltr">{{ $setting->name }}</p>
                            <p class="font-medium text-12px text-gray">#{{ $setting->id }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('panel.v1.admin.system.settings.save', ['id' => $setting->id]) }}"
                        class="p-4 sm:p-5 space-y-3">
                        @csrf
                        @if ($isLong)
                            <textarea name="value" rows="8"
                                class="textarea textarea-bordered w-full rounded-12px border-d9 font-medium text-13px text-primary focus:outline-none focus:border-primary"
                                dir="ltr">{{ $pretty }}</textarea>
                        @else
                            <input type="text" name="value" value="{{ $pretty }}"
                                class="input input-bordered w-full h-12 rounded-12px border-d9 font-medium text-14px text-primary focus:outline-none focus:border-primary"
                                dir="ltr">
                        @endif
                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 h-11 px-5 rounded-12px bg-primary text-white font-semibold text-14px hover:opacity-95 transition">
                                <span class="icon-[tabler--device-floppy] size-4"></span>
                                حفظ
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
