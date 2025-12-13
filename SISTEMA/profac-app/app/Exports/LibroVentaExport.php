<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LibroVentaExport implements FromView, WithStyles
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFinal;

    public function __construct($data, $fechaInicio, $fechaFinal)
    {
        $this->data = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFinal = $fechaFinal;
    }

    public function view(): View
    {
        return view('excel.libroventarep', [
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFinal' => $this->fechaFinal,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:J2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3:J3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal('center');

        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return [
            'A'  => ['alignment' => ['horizontal' => 'center']],
            'B'  => ['alignment' => ['horizontal' => 'center']],
            'C'  => ['alignment' => ['horizontal' => 'center']],
            'D'  => ['alignment' => ['horizontal' => 'center']],
            'E'  => ['alignment' => ['horizontal' => 'center']],
            'F'  => ['alignment' => ['horizontal' => 'center']],
            'G'  => ['alignment' => ['horizontal' => 'center']],
            'H'  => ['alignment' => ['horizontal' => 'center']],
            'I'  => ['alignment' => ['horizontal' => 'center']],
            'J'  => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
