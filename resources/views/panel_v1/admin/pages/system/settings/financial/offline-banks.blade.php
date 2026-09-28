@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
@endphp

<div class="space-y-6">
    <form action="{{ route('panel.v1.admin.system.settings.store') }}" method="post" class="max-w-xl space-y-4">
        @csrf
        <input type="hidden" name="page" value="financial">
        <input type="hidden" name="name" value="{{ \App\Models\Setting::$offlineBanksName }}">

        <label class="pv1-switch-wrap">
            <input type="hidden" name="value[offline_banks_status]" value="0">
            <input type="checkbox" name="value[offline_banks_status]" id="offline_banks_statusSwitch" value="1"
                {{ (!empty($itemValue) and !empty($itemValue['offline_banks_status']) and $itemValue['offline_banks_status']) ? 'checked' : '' }}
                class="pv1-switch">
            <span class="pv1-switch-label">{{ trans('update.offline_banks_status') }}</span>
        </label>

        <button type="submit" class="pv1-btn-primary">
            <span class="icon-[tabler--device-floppy] size-4"></span>
            {{ trans('admin/main.save_change') }}
        </button>
    </form>

    <div class="rounded-14px border border-d9 overflow-hidden">
        <div class="px-4 sm:px-5 py-3.5 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between gap-3">
            <h3 class="font-bold text-15px text-primary">{{ trans('update.offline_banks_credits') }}</h3>
            <button type="button"
                data-path="{{ route('panel.v1.admin.system.settings.offline-banks.form') }}"
                class="js-add-offline-banks inline-flex items-center gap-2 h-10 px-4 rounded-12px bg-primary text-white font-semibold text-13px">
                <span class="icon-[tabler--plus] size-4"></span>
                {{ trans('update.add_bank') }}
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-start">
                <thead>
                    <tr class="border-b border-d9 bg-white">
                        <th class="px-4 py-3 font-bold text-13px text-primary">{{ trans('admin/main.logo') }}</th>
                        <th class="px-4 py-3 font-bold text-13px text-primary">{{ trans('admin/main.title') }}</th>
                        <th class="px-4 py-3 font-bold text-13px text-primary text-center">{{ trans('update.specifications') }}</th>
                        <th class="px-4 py-3 font-bold text-13px text-primary text-center">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($offlineBanks ?? collect()) as $offlineBank)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3">
                                @if ($offlineBank->logo)
                                    <img src="{{ $offlineBank->logo }}" alt="" class="w-12 h-12 object-contain rounded-8px border border-d9 bg-white">
                                @else
                                    <span class="text-gray text-13px">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-14px text-primary">{{ $offlineBank->title }}</td>
                            <td class="px-4 py-3 text-center font-medium text-14px text-gray">{{ $offlineBank->specifications->count() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        data-path="{{ route('panel.v1.admin.system.settings.offline-banks.edit', ['id' => $offlineBank->id]) }}"
                                        class="js-edit-offline-banks inline-flex items-center gap-1.5 h-9 px-3 rounded-12px border border-d9 font-semibold text-13px text-primary hover:bg-[#FAFAF4]">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                        {{ trans('admin/main.edit') }}
                                    </button>
                                    @include('panel_v1.admin.components.settings-delete-button', [
                                        'url' => route('panel.v1.admin.system.settings.offline-banks.delete', ['id' => $offlineBank->id]),
                                        'btnClass' => 'inline-flex items-center gap-1.5 h-9 px-3 rounded-12px border border-[#FECACA] bg-[#FEE2E2] font-semibold text-13px text-[#DC2626]',
                                        'btnText' => trans('admin/main.delete'),
                                    ])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center font-medium text-14px text-gray">لا توجد بنوك مضافة بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
