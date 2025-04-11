<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FacturasAnuladasExport implements FromView, WithStyles
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
        return view('excel.facturasanuladasrep', [
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFinal' => $this->fechaFinal,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:G2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3:G3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:G3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B50:E51')->getAlignment()->setHorizontal('center');

        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Ajustar el alto de las filas para las firmas
        $sheet->getRowDimension('50')->setRowHeight(30);
        $sheet->getRowDimension('51')->setRowHeight(30);

        return [
            'A'  => ['alignment' => ['horizontal' => 'center']],
            'B'  => ['alignment' => ['horizontal' => 'center']],
            'C'  => ['alignment' => ['horizontal' => 'center']],
            'D'  => ['alignment' => ['horizontal' => 'center']],
            'E'  => ['alignment' => ['horizontal' => 'center']],
            'F'  => ['alignment' => ['horizontal' => 'center']],
            'G'  => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
