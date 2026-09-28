@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $tabs = [
        'basic' => ['label' => trans('admin/main.basic'), 'url' => route('panel.v1.admin.system.settings.financial')],
        'offline_banks' => ['label' => trans('admin/main.offline_banks_credits'), 'url' => route('panel.v1.admin.system.settings.financial', ['tab' => 'offline_banks'])],
    ];
    $current = $activeTab ?? 'basic';
@endphp
@include('panel_v1.admin.components.settings-form-styles')
<div class="space-y-6 sm:space-y-8 pb-8 pv1-settings">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'الإعدادات المالية',
        'subtitle' => 'الضريبة، الحد الأدنى للسحب، والبنوك للتحويل البنكي',
    ])
        @slot('actions')
            <a href="{{ $hubUrl ?? route('panel.v1.admin.system.section', ['section' => 'settings']) }}"
                class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة للإعدادات
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-d9 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap gap-2 p-3 sm:p-4 border-b border-d9 bg-[#FAFAF4]">
            @foreach ($tabs as $key => $tab)
                <a href="{{ $tab['url'] }}"
                    class="h-10 px-4 rounded-12px font-semibold text-13px transition inline-flex items-center {{ $current === $key ? 'bg-primary text-white' : 'bg-white text-primary border border-d9' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <div class="p-4 sm:p-6 space-y-6">
            @if ($current === 'basic')
                @include('panel_v1.admin.pages.system.settings.financial.basic', [
                    'itemValue' => (!empty($settings) && !empty($settings['financial'])) ? $settings['financial']->value : '',
                ])
            @endif

            @if ($current === 'offline_banks')
                @include('panel_v1.admin.pages.system.settings.financial.offline-banks', [
                    'itemValue' => (!empty($settings) && !empty($settings['offline_banks'])) ? $settings['offline_banks']->value : '',
                    'offlineBanks' => $offlineBanks ?? collect(),
                ])
            @endif
        </div>
    </div>
</div>

{{-- Offline bank modal host --}}
<dialog id="offline-bank-dialog" class="modal">
    <div class="modal-box max-w-2xl rounded-14px p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
            <h3 class="font-bold text-16px text-primary" id="offline-bank-dialog-title">{{ trans('update.add_bank') }}</h3>
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost">✕</button></form>
        </div>
        <div id="offline-bank-dialog-body" class="p-5 pv1-settings"></div>
    </div>
    <form method="dialog" class="modal-backdrop"><button type="submit" aria-label="إغلاق">إغلاق</button></form>
</dialog>

@push('scripts')
<script>
(function () {
    const dialog = document.getElementById('offline-bank-dialog');
    const body = document.getElementById('offline-bank-dialog-body');
    const title = document.getElementById('offline-bank-dialog-title');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    async function openBankForm(url, heading) {
        title.textContent = heading;
        body.innerHTML = '<p class="font-medium text-14px text-gray">جاري التحميل...</p>';
        dialog.showModal();
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            body.innerHTML = data.html || '';
            bindBankForm();
        } catch (e) {
            body.innerHTML = '<p class="text-red-600 font-semibold">تعذّر التحميل</p>';
        }
    }

    function bindBankForm() {
        const root = body.querySelector('#addOfflineBankForm');
        if (!root) return;

        body.querySelector('.js-add-specification')?.addEventListener('click', function () {
            const id = 'new_' + Date.now();
            const lists = body.querySelector('.js-specifications-lists');
            const row = document.createElement('div');
            row.className = 'js-specification-card grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-3 mb-3 items-end';
            row.innerHTML = `
                <div class="pv1-field mb-0">
                    <label>{{ trans('update.specification') }}</label>
                    <input type="text" name="specifications[${id}][name]" class="pv1-input">
                </div>
                <div class="pv1-field mb-0">
                    <label>{{ trans('update.value') }}</label>
                    <input type="text" name="specifications[${id}][value]" class="pv1-input">
                </div>
                <button type="button" class="js-remove-specification pv1-btn-danger h-12">حذف</button>
            `;
            lists.appendChild(row);
            row.querySelector('.js-remove-specification').addEventListener('click', () => row.remove());
        });

        body.querySelectorAll('.js-remove-specification').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('.js-specification-card')?.remove());
        });

        body.querySelector('.js-save-bank')?.addEventListener('click', async function () {
            const action = root.getAttribute('data-action');
            const fd = new FormData();
            root.querySelectorAll('input, select, textarea').forEach(el => {
                if (!el.name) return;
                if (el.type === 'checkbox' && !el.checked) return;
                fd.append(el.name, el.value);
            });
            if (csrf) fd.append('_token', csrf);

            const res = await fetch(action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            });
            if (res.status === 422) {
                const err = await res.json();
                alert(Object.values(err.errors || {}).flat().join('\n') || 'يرجى التحقق من الحقول المطلوبة');
                return;
            }
            const data = await res.json();
            if (data.code === 200) {
                window.location.reload();
            }
        });

        body.querySelector('.close-swl')?.addEventListener('click', () => dialog.close());
    }

    document.querySelectorAll('.js-add-offline-banks').forEach(btn => {
        btn.addEventListener('click', () => openBankForm(btn.dataset.path, '{{ trans('update.add_bank') }}'));
    });
    document.querySelectorAll('.js-edit-offline-banks').forEach(btn => {
        btn.addEventListener('click', () => openBankForm(btn.dataset.path, '{{ trans('admin/main.edit') }}'));
    });
})();
</script>
@endpush
@endsection
