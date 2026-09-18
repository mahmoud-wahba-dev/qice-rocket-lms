<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * QICE unified Excel styling — بدون شعارات/لوجوهات
 * هيدر #0F3D36 + نص أبيض Cairo 11 Bold + صفوف متناوبة + بوردر #E8E8E8
 */
trait QiceExportStyling
{
    /**
     * تنسيق عام للشيت — يُطبق تلقائياً عبر WithStyles
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $headerRange = "A1:{$highestColumn}1";

        // هيدر — غامق موحد
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Cairo',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F3D36'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0F3D36'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        if ($highestRow > 1) {
            $dataRange = "A2:{$highestColumn}{$highestRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => [
                    'size' => 10,
                    'name' => 'Cairo',
                    'color' => ['rgb' => '1F2937'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E8E8E8'],
                    ],
                ],
            ]);

            // صفوف متناوبة #F9FAF5 للقراءة — بدون لوجو
            for ($r = 2; $r <= $highestRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(18);
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F9FAF5');
                }
            }

            // فلتر + تجميد الهيدر
            $sheet->setAutoFilter($headerRange);
            $sheet->freezePane('A2');
        }

        // اتجاه RTL للعربية — يحسن عرض العناوين العربية بدون شعار
        $sheet->setRightToLeft(true);

        // وضوح الحقول — عرض عمود تلقائي + حد أدنى 14 وحد أقصى 40
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($c = 1; $c <= $highestColumnIndex; $c++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            // ضمان حد أدنى للعرض حتى لا تظهر الحقول مضغوطة
            $sheet->getColumnDimension($col)->setWidth(max(14, $sheet->getColumnDimension($col)->getWidth()));
        }
        // لف النص للعناوين الطويلة + زيادة ارتفاع الهيدر لظهورها كاملة
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
        if ($highestRow > 1) {
            $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(true);
        }

        return [];
    }

    /**
     * عرض افتراضي في حال لم يطبق ShouldAutoSize (احتياطي)
     */
    public function columnWidths(): array
    {
        return [];
    }
}
