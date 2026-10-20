<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $casts = [
        'incluir_reporte_compras' => 'boolean',
        'anulado_at' => 'datetime',
    ];

    public function scopeReporte(Builder $query): Builder
    {
        return $query->join('proveedores as p', 'p.id', '=', 'gastos.proveedor_id')
            ->leftJoin('users as anulador', 'anulador.id', '=', 'gastos.anulado_por')
            ->select('gastos.*', 'p.nombre as proveedor', 'anulador.name as anulado_por_nombre');
    }

    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query->whereBetween('gastos.fecha_emision', [$filtros['desde'], $filtros['hasta']])
            ->when(trim($filtros['factura'] ?? '') !== '', fn (Builder $query) =>
                $query->where('gastos.numero_factura', 'like', '%' . trim($filtros['factura']) . '%'))
            ->when($filtros['proveedor'] ?? '', fn (Builder $query, $proveedor) =>
                $query->where('gastos.proveedor_id', $proveedor));
    }

    public function getEstadoReporteAttribute(): string
    {
        if ($this->estado === 'anulado') {
            return 'Anulado';
        }

        return $this->incluir_reporte_compras ? 'Pasado al reporte de compras' : 'Registrado';
    }
}
