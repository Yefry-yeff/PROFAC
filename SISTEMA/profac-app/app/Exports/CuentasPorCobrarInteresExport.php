<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CuentasPorCobrarInteresExport implements FromCollection, WithHeadings, ShouldAutoSize
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
                ->select('factura.numero_factura as numero_factura','factura.cai as correlativo', 'factura.cliente_id','factura.nombre_cliente','factura.numero_factura as documento', 'factura.fecha_emision','factura.fecha_vencimiento', 'factura.total', DB::raw('factura.total-factura.pendiente_cobro'), DB::raw('@numeroDias := TIMESTAMPDIFF(DAY, fecha_vencimiento, DATE(NOW()) )'), DB::raw('@interesDiario:=0 as interesInicia'), DB::raw('if(@numeroDias < 0, @interesDiario:=0, @interesDiario:= @numeroDias*0.1083333333)'), DB::raw('factura.pendiente_cobro + @interesDiario'))
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
            'Documento',
            'Fecha Emision',
            'Fecha Vencimiento',
            'Cargo',
            'Abonos',
            'Dias',
            'Intereses Inicial',
            'Interes Diario',
            'Acumulado',
        ];
    }
}
