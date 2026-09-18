<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Exports\Concerns\QiceExportStyling;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Instructor panel_v1 payout history export (Arabic columns).
 */
class InstructorPayoutExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize {
    use QiceExportStyling;

protected $payouts;

    public function __construct($payouts)
    {
        $this->payouts = $payouts;
    }

    public function collection()
    {
        return $this->payouts instanceof Collection
            ? $this->payouts
            : collect($this->payouts);
    }

    public function headings(): array
    {
        return [
            'رقم العملية',
            'التاريخ',
            'الوقت',
            'نوع المعاملة',
            'المبلغ',
            'الحالة',
            'البنك',
            'ملاحظات',
        ];
    }

    public function map($payout): array
    {
        $statusLabels = [
            'waiting' => 'قيد المعالجة',
            'done' => 'مكتمل',
            'reject' => 'مرفوض',
        ];

        $bankTitle = '—';
        try {
            $bankTitle = $payout->userSelectedBank->bank->title ?? '—';
        } catch (\Throwable $e) {
            $bankTitle = '—';
        }

        $created = (int) ($payout->created_at ?? 0);

        return [
            '#' . $payout->id,
            $created ? date('Y/m/d', $created) : '—',
            $created ? date('H:i', $created) : '—',
            'طلب سحب أرباح',
            handlePrice($payout->amount),
            $statusLabels[$payout->status] ?? (string) $payout->status,
            $bankTitle,
            '—',
        ];
    }
}
