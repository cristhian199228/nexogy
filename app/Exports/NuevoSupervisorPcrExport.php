<?php

namespace App\Exports;

use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\FichaPaciente;
use App\Paciente;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\PcrPruebaMolecular;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;



class NuevoSupervisorPcrExport implements FromView, ShouldAutoSize, WithEvents, WithDrawings
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }


    public function view(): View
    {
        return view('excel.reportepcr', [
            'fichas' => $this->data
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                // All headers - set font size to 14
                $cellRange = 'B2:R3';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);

                // Apply array of styles to B2:G8 cell range
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => '000000'],
                        ]
                    ]
                ];


                $event->sheet->getDelegate()->getStyle('B2:N3')->applyFromArray($styleArray);

                // Set first row to height 20
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(50);
                $event->sheet->getDelegate()->getColumnDimension('A')->setAutoSize(false);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth("20");
                //$event->sheet->getDelegate()->setHeight(1, 50);
                $event->sheet->getRowDimension(1)->setRowHeight(60);
                $event->sheet->getRowDimension(2)->setRowHeight(100);

                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth("40");
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth("50");
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth("20");
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth("20");
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth("20");
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth("20");
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth("20");
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth("30");
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth("60");
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth("30");
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth("40");
                $event->sheet->getDelegate()->getColumnDimension('M')->setWidth("20");
                $event->sheet->getDelegate()->getColumnDimension('N')->setWidth("20");

                // Set A1:D4 range to wrap text in cells
                $event->sheet->getDelegate()->getStyle('A1:N3')
                    ->getAlignment()->setWrapText(true);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('/img/logo_prueba_rapidez.png'));
        $drawing->setHeight(80);
        $drawing->setOffsetX(105);
        $drawing->setOffsetY(30);
        $drawing->setCoordinates('B2');

        return $drawing;
    }
}
