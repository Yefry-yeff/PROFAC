<?php

namespace App\Exports;

use App\Models\ModelCompra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ComprasMesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    
    public function __construct($mes)
    {
        $this->mes = $mes;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //return ModelCompra::all();
        return ModelCompra::select("id","numero_factura","codigo_cai","fecha_vencimiento","fecha_emision","fecha_recepcion","isv_compra","sub_total","total","debito","numero_orden")->whereMonth('fecha_emision',$this->mes)->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Numero de Factura',
            'Código CAI',
            'Fecha de Vencimiento',
            'Fecha de Emisión',
            'Fecha de Recepción',
            'ISV Compra',
            'Sub-Total',
            'Total',
            'Débito',
            'Número de Orden',
        ];
    }
}
