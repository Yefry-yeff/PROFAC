<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class BoletasCompras
{
    public static function filtros(array $datos): array
    {
        return validator($datos, [
            'fechaInicio' => 'required|date_format:Y-m-d',
            'fechaFinal' => 'required|date_format:Y-m-d|after_or_equal:fechaInicio',
            'buscar' => 'nullable|string|max:255',
        ])->validate();
    }

    public static function historial(array $filtros): Builder
    {
        return DB::table('boleta_compra as b')
            ->whereBetween('b.fecha', [$filtros['fechaInicio'], $filtros['fechaFinal']])
            ->when(trim($filtros['buscar'] ?? '') !== '', function ($query) use ($filtros) {
                $texto = '%' . trim($filtros['buscar']) . '%';
                $query->where(function ($query) use ($texto) {
                    $query->where('b.numero_boleta', 'like', $texto)
                        ->orWhere('b.cliente', 'like', $texto)
                        ->orWhere('b.rtn_dni', 'like', $texto)
                        ->orWhere('b.direccion', 'like', $texto);
                });
            });
    }

    public static function aplicarCompras(Builder $query, array $filtros): Builder
    {
        return $query->where('b.estado', 1)->where('b.incluir_reporte_compras', true)
            ->whereBetween('b.fecha', [$filtros['desde'], $filtros['hasta']])
            ->when(trim($filtros['factura'] ?? '') !== '', fn ($query) =>
                $query->where('b.numero_boleta', 'like', '%' . trim($filtros['factura']) . '%'))
            ->when($filtros['proveedor'] ?? '', fn ($query, $id) => $query
                ->whereNotNull('b.rtn_dni')->where('b.rtn_dni', '<>', '')
                ->whereIn('b.rtn_dni', DB::table('proveedores')->select('rtn')->where('id', $id)));
    }

    public static function documentos(Builder $query): Builder
    {
        $importes = DB::table('boleta_compra_detalle')->select('boleta_compra_id')
            ->selectRaw('SUM(importe) AS importe_exento')->groupBy('boleta_compra_id');
        return $query->leftJoin('cai_boleta_compra as cai', 'cai.id', '=', 'b.cai_boleta_id')
            ->leftJoinSub($importes, 'importes', 'importes.boleta_compra_id', '=', 'b.id')
            ->select('b.rtn_dni as rtn', 'b.cliente as nombre', 'cai.cai',
                'b.numero_boleta as numero_factura', 'b.fecha as fecha_emision',
                'importes.importe_exento', 'b.estado', 'b.incluir_reporte_compras')
            ->selectRaw("0 AS base_15, 'boleta' AS origen")->orderBy('b.id');
    }

    public static function estado(object $boleta): string
    {
        return (int) $boleta->estado !== 1 ? 'Anulada'
            : ($boleta->incluir_reporte_compras ? 'Pasada al reporte de compras' : 'Registrada');
    }
}
