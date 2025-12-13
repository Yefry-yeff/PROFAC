<?php
namespace App\Exports;

use App\Models\ModelFactura;
use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class declaracionesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping
{
    public function collection()
    {
        return ModelFactura::all();
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->numero_factura,
            $row->cai,
            $row->numero_secuencia_cai,
            $row->nombre_cliente,
            $row->rtn,
            $row->sub_total,
            $row->isv,
            $row->total,
            $row->pendiente_cobro,
            $row->credito,
            $row->fecha_emision,
            $row->fecha_vencimiento,
            $row->tipo_pago_id,         
            $row->cai_id,
            $row->estado_venta_id,
            $row->cliente_id,
            $row->vendedor,
            $row->monto_comision,
            $row->tipo_venta_id,
            $row->estado_factura_id,
            $row->comision_estado_pagado,
            Date::dateTimeToExcel($row->created_at),
            Date::dateTimeToExcel($row->updated_at),
        ];
    }

    public function headings(): array
    {
        return [
        "id",
        "numero_factura",
        "cai",
        "numero_secuencia_cai",
        "nombre_cliente",
        "rtn",
        "sub_total",
        "isv",
        "total",
        "pendiente_cobro",
        "credito",
        "fecha_emision",
        "fecha_vencimiento",
        "tipo_pago_id",
        "cai_id",
        "estado_venta_id",
        "cliente_id",
        "vendedor",
        "monto_comision",
        "tipo_venta_id",
        "estado_factura_id",
        "comision_estado_pagado",
        "created_at",
        "updated_at"
    ];
    }

    public function styles(Worksheet $sheet)
    {
       /* return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true], 'borderStyle'=>2],

            // Styling a specific cell by coordinate.
           // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            'C'  => ['font' => ['size' => 16]],
        ];*/

        
          $sheet->getStyle('A1:X1')->getFont()->setBold(true);
          $sheet->getStyle('A1:X1')->getFill()->setFillType(Fill::FILL_SOLID);
          $sheet->getStyle('A1:X1')->getFill()->getStartColor()->setARGB('2884bd');
          $sheet->getStyle('A1:X1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
          $sheet->getStyle('W')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY );
          $sheet->getStyle('X')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY );
          return ;
        
    }
}

