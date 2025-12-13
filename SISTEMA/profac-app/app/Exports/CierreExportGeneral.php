<?php

namespace App\Exports;


use App\Models\bitacoraCierre;
use App\Models\CierreDiario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class CierreExportGeneral implements FromCollection, WithHeadings, ShouldAutoSize
{

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return CierreDiario::select('fecha','nombre_userCierre','estadoDescripcion', 'factura','cliente','vendedor','subtotal','imp_venta','total','tipoFactura','tipo', 'created_at')->get();
        //return CierreDiario::select('fecha','nombre_userCierre','estadoDescripcion', 'factura','cliente','vendedor','subtotal','imp_venta','total','tipoFactura','tipo', 'created_at')->where('estado_cierre',1)->get();
    }

    public function headings(): array
    {
        return [
            'FECHA DE CIERRE',
            'REGISTRADO POR',
            'ESTADO DE CAJA',
            'FACTURA',
            'CLIENTE',
            'VENDEDOR',
            'SUBTOTAL FACTURADO',
            'ISV FACTURADO',
            'TOTAL FACTURADO',
            'CALIDAD DE FACTURA',
            'TIPO DE CLIENTE',
            'FECHA DEL REGISTRO',
        ];
    }
}
