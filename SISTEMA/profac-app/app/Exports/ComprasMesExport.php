<?php

namespace App\Exports;

use App\Support\FiltrosCompras;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ComprasMesExport extends DefaultValueBinder implements FromCollection, WithHeadings, ShouldAutoSize,
    WithColumnFormatting, WithCustomValueBinder, WithStrictNullComparison
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
        $importesCompras = DB::table('compra_has_producto as detalle')
            ->join('producto', 'producto.id', '=', 'detalle.producto_id')
            ->select('detalle.compra_id')
            ->selectRaw('SUM(CASE WHEN producto.isv = 0 THEN detalle.sub_total_producto ELSE 0 END) AS importe_exento')
            ->selectRaw('SUM(CASE WHEN producto.isv = 15 THEN detalle.sub_total_producto ELSE 0 END) AS base_15')
            ->groupBy('detalle.compra_id');
        $importesGastos = DB::table('gasto_detalles')->select('gasto_id')
            ->selectRaw('SUM(CASE WHEN isv_porcentaje = 0 THEN subtotal ELSE 0 END) AS importe_exento')
            ->selectRaw('SUM(CASE WHEN isv_porcentaje = 15 THEN subtotal ELSE 0 END) AS base_15')
            ->groupBy('gasto_id');
        $compras = FiltrosCompras::aplicar(DB::table('compra')
            ->join('proveedores', 'proveedores.id', '=', 'compra.proveedores_id')
            ->leftJoinSub($importesCompras, 'importes', 'importes.compra_id', '=', 'compra.id')
            ->select('proveedores.rtn', 'proveedores.nombre', 'compra.codigo_cai as cai',
                'compra.numero_factura', 'compra.fecha_emision', 'importes.importe_exento', 'importes.base_15')
            ->where('compra.estado_compra_id', 1), $this->filtros, 'compra', 'proveedores_id')
            ->orderBy('compra.id')->get();
        $gastos = FiltrosCompras::aplicar(
            DB::table('gastos')
                ->join('proveedores', 'proveedores.id', '=', 'gastos.proveedor_id')
                ->leftJoinSub($importesGastos, 'importes', 'importes.gasto_id', '=', 'gastos.id')
                ->select('proveedores.rtn', 'proveedores.nombre', 'gastos.cai',
                    'gastos.numero_factura', 'gastos.fecha_emision', 'importes.importe_exento', 'importes.base_15')
                ->where('incluir_reporte_compras', true)->where('gastos.estado', 'activo'),
            $this->filtros, 'gastos', 'proveedor_id')
            ->orderBy('gastos.id')->get();
        return $compras->concat($gastos)->map(function ($documento) {
            $exento = round((float) $documento->importe_exento, 2);
            $base15 = round((float) $documento->base_15, 2);
            $fecha = \Carbon\Carbon::parse($documento->fecha_emision)->format('d/m/Y');
            return [
                'rtn' => $documento->rtn,
                'clase_documento' => 'FA',
                'cai' => $documento->cai,
                'numero_factura' => $documento->numero_factura,
                'proveedor' => $documento->nombre,
                'fecha_emision' => $fecha,
                'fecha_contable' => $fecha,
                'importe_exento' => $exento,
                'base_15' => $base15,
                'monto_al_costo' => round($exento + $base15, 2),
                'monto_al_gasto' => round($exento + $base15, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '200-RTN',
            '600-Clase de documento',
            '7-CAI',
            '8-Nº documento (establecimiento - punto de emisión - tipo documento - correlativo)',
            '71-Nº Nombre del proveedor',
            '900-Fecha emisión',
            '100-Fecha contable',
            '110-Importe exento',
            '1511-Importe base 15%',
            '270-Monto al costo',
            '280-Monto al gasto',
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($value !== null && ($cell->getRow() === 1 || !in_array($cell->getColumn(), ['H', 'I', 'J', 'K'], true))) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return ['H' => '#,##0.00', 'I' => '#,##0.00', 'J' => '#,##0.00', 'K' => '#,##0.00'];
    }
}
