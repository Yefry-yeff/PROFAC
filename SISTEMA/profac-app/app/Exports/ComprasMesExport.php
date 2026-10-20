<?php

namespace App\Exports;

use App\Models\ModelCompra;
use App\Support\FiltrosCompras;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ComprasMesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    
    private array $filtros;

    public function __construct(string|array $mes)
    {
        if (is_string($mes)) {
            validator(['mes' => $mes], ['mes' => 'required|date_format:Y-m'])->validate();
            $fecha = \Carbon\Carbon::createFromFormat('!Y-m', $mes);
            $mes = ['desde' => $fecha->copy()->startOfMonth()->toDateString(),
                'hasta' => $fecha->copy()->endOfMonth()->toDateString()];
        }
        $this->filtros = FiltrosCompras::validar($mes);
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): \Illuminate\Support\Enumerable
    {
        $compras = FiltrosCompras::aplicar(
            ModelCompra::select("id","numero_factura","codigo_cai","fecha_vencimiento","fecha_emision","fecha_recepcion","isv_compra","sub_total","total","debito","numero_orden")
                ->where('estado_compra_id', 1), $this->filtros, 'compra', 'proveedores_id')->get()
            ->map(fn ($compra) => array_merge($compra->toArray(), ['estado' => 'Compra']));
        $gastos = FiltrosCompras::aplicar(
            DB::table('gastos')->where('incluir_reporte_compras', true)->where('estado', 'activo'),
            $this->filtros, 'gastos', 'proveedor_id')
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
