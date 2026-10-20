<?php

namespace App\Exports;

use App\Models\ModelCompra;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ComprasMesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    
    public function __construct(private string $mes)
    {
        validator(['mes' => $mes], ['mes' => 'required|date_format:Y-m'])->validate();
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): \Illuminate\Support\Enumerable
    {
        [$anio, $mes] = explode('-', $this->mes);
        $compras = ModelCompra::select("id","numero_factura","codigo_cai","fecha_vencimiento","fecha_emision","fecha_recepcion","isv_compra","sub_total","total","debito","numero_orden")
            ->whereYear('fecha_emision', $anio)->whereMonth('fecha_emision', $mes)->get()
            ->map(fn ($compra) => array_merge($compra->toArray(), ['estado' => 'Compra']));
        $gastos = DB::table('gastos')->where('incluir_reporte_compras', true)->where('estado', 'activo')
            ->whereYear('fecha_emision', $anio)->whereMonth('fecha_emision', $mes)
            ->orderBy('id')->get()->map(fn ($gasto) => [
                'id' => 'G-' . $gasto->id, 'numero_factura' => $gasto->numero_factura,
                'codigo_cai' => $gasto->cai, 'fecha_vencimiento' => $gasto->fecha_vencimiento,
                'fecha_emision' => $gasto->fecha_emision, 'fecha_recepcion' => $gasto->fecha_recepcion,
                'isv_compra' => $gasto->isv, 'sub_total' => $gasto->subtotal, 'total' => $gasto->total,
                'debito' => null, 'numero_orden' => 'GASTO-' . $gasto->id,
                'estado' => 'Pasado al reporte de compras',
            ]);
        return $compras->toBase()->concat($gastos);
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
            'Estado',
        ];
    }
}
