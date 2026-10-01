<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Exports\Concerns\QiceExportStyling;

class CourseStudentsListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use QiceExportStyling;

    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows instanceof Collection ? $this->rows : collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'ID',
            'الاسم',
            'البريد',
            'التقييمات(5)',
            'التعلم %',
            'مجموعة المستخدم',
            'الدخل',
            'تاريخ الشراء',
            'الحالة',
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'] ?? '',
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['rate'] ?? '—',
            $row['learning'] ?? 0,
            $row['group'] ?? '—',
            $row['income_raw'] ?? ($row['income'] ?? '—'),
            $row['purchase_date'] ?? '—',
            $row['status_label'] ?? '—',
        ];
    }
}
