<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstructorCoursePerformanceExport implements FromCollection, WithHeadings, WithMapping
{
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
            'المتدرب',
            'البريد',
            'التقدم %',
            'نشاط التعلم',
            'الاختبارات المجتازة',
            'التكليفات المجتازة',
            'الشهادات',
            'تاريخ التسجيل',
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['progress'] ?? 0,
            $row['activity'] ?? '—',
            $row['exams'] ?? 0,
            $row['assignments'] ?? 0,
            $row['certificates'] ?? 0,
            $row['joined_at'] ?? '—',
        ];
    }
}
