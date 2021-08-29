<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportReporteComplejo implements FromView, ShouldAutoSize, WithEvents, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        // TODO: Implement view() method.
        return view('excel.reporte_diario_complejo', [
            'reporte' => $this->data,
        ]);
    }

    public function registerEvents(): array
    {
        // TODO: Implement registerEvents() method.
        return [
            AfterSheet::class   => function (AfterSheet $event) {
                $cellRange = 'A1:C27';

                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ]
                    ]
                ];
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);

            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // TODO: Implement styles() method.
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('B2')->getFont()->setBold(true);
        $sheet->getStyle('C2')->getFont()->setBold(true);
    }
}
