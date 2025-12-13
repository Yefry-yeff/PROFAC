<?php

namespace App\Exports;


use App\Models\bitacoraCierre;
use App\Models\CierreDiario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class CierreExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct($bitacoraCierre)
    {
        $this->bitacoraCierre = $bitacoraCierre;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return CierreDiario::select('fecha','nombre_userCierre','estadoDescripcion', 'factura','cliente','vendedor','subtotal','imp_venta','total','tipoFactura','tipo', 'textoCobro','created_at')->where('bitacoraCierre_id',$this->bitacoraCierre)->get();
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
            'PAGO POR',
            'FECHA DEL REGISTRO',
        ];
    }


}
