<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;


class CuentasPorCobrarExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($cliente)
    {
        $this->cliente = $cliente;
    }

    public function collection()
    {
       return DB::table('factura')
                ->join('cliente', 'factura.cliente_id', '=', 'cliente.id')
                ->select('factura.numero_factura as numero_factura','factura.cai as correlativo,', 'factura.cliente_id','factura.nombre_cliente', 'factura.fecha_emision','factura.fecha_vencimiento', 'factura.total', DB::raw('factura.total-factura.pendiente_cobro'), 'factura.pendiente_cobro')
                ->where('factura.pendiente_cobro', '<>', 0)
                ->where('factura.cliente_id', '=', $this->cliente)
                ->get();
    }

    public function headings(): array
    {
        return [
            'Numero Factura',
            'Correlativo',
            'ID Cliente',
            'Cliente',
            'Fecha Emision',
            'Fecha Vencimiento',
            'Cargo',
            'Credito',
            'Saldo',

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


          $sheet->getStyle('A1:I1')->getFont()->setBold(true);
          $sheet->getStyle('A1:I1')->getFont()->getColor()->setARGB('FFFFFF');
          $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID);
          $sheet->getStyle('A1:I1')->getFill()->getStartColor()->setARGB('17378C');
          $sheet->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle('thin');

          return ;

    }


}
