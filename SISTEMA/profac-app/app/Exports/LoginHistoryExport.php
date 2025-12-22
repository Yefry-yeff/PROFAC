<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoginHistoryExport implements FromView, WithStyles
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($data, $fechaInicio, $fechaFin)
    {
        $this->data = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function view(): View
    {
        return view('excel.login-history', [
            'data' => $this->data,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells para los títulos
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        
        // Estilos para los títulos
        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:E2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3:E3')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1:E3')->getAlignment()->setHorizontal('center');
        
        // Estilos para los encabezados de columna
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');

        // Auto tamaño para todas las columnas
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return [
            'A' => ['alignment' => ['horizontal' => 'center']],
            'B' => ['alignment' => ['horizontal' => 'left']],
            'C' => ['alignment' => ['horizontal' => 'center']],
            'D' => ['alignment' => ['horizontal' => 'left']],
            'E' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
