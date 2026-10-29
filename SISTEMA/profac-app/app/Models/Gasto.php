<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    public const ESTADOS_REPORTE = [
        'registrado' => 'Registrado',
        'compras' => 'Pasado al reporte de compras',
        'anulado' => 'Anulado',
    ];

    public const COLUMNAS_ORDEN = [
        'id' => 'gastos.id',
        'numero_factura' => 'gastos.numero_factura',
        'proveedor' => 'p.nombre',
        'fecha_emision' => 'gastos.fecha_emision',
        'total' => 'gastos.total',
        'estado' => 'estado_orden',
        'anulado_por' => 'anulador.name',
        'anulado_at' => 'gastos.anulado_at',
        'motivo_anulacion' => 'gastos.motivo_anulacion',
    ];

    protected $table = 'gastos';

    protected $casts = [
        'incluir_reporte_compras' => 'boolean',
        'anulado_at' => 'datetime',
    ];

    public function scopeReporte(Builder $query): Builder
    {
        return $query->join('proveedores as p', 'p.id', '=', 'gastos.proveedor_id')
            ->leftJoin('users as anulador', 'anulador.id', '=', 'gastos.anulado_por')
            ->select('gastos.*', 'p.nombre as proveedor', 'anulador.name as anulado_por_nombre')
            ->selectRaw("CASE WHEN gastos.estado = 'anulado' THEN 'Anulado'
                WHEN gastos.incluir_reporte_compras = 1 THEN 'Pasado al reporte de compras'
                ELSE 'Registrado' END as estado_orden");
    }

    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query->whereBetween('gastos.fecha_emision', [$filtros['desde'], $filtros['hasta']])
            ->when(trim($filtros['factura'] ?? '') !== '', fn (Builder $query) =>
                $query->where('gastos.numero_factura', 'like', '%' . trim($filtros['factura']) . '%'))
            ->when($filtros['proveedor'] ?? '', fn (Builder $query, $proveedor) =>
                $query->where('gastos.proveedor_id', $proveedor))
            ->when(($filtros['estado'] ?? '') === 'anulado', fn (Builder $query) =>
                $query->where('gastos.estado', 'anulado'))
            ->when(in_array($filtros['estado'] ?? '', ['registrado', 'compras'], true), fn (Builder $query) =>
                $query->where('gastos.estado', 'activo')
                    ->where('gastos.incluir_reporte_compras', $filtros['estado'] === 'compras'));
    }

    public function scopeOrdenar(Builder $query, string $columna, string $direccion): Builder
    {
        validator(compact('columna', 'direccion'), [
            'columna' => ['required', \Illuminate\Validation\Rule::in(array_keys(self::COLUMNAS_ORDEN))],
            'direccion' => ['required', \Illuminate\Validation\Rule::in(['asc', 'desc'])],
        ])->validate();

        $query->orderBy(self::COLUMNAS_ORDEN[$columna], $direccion);

        return $columna === 'id' ? $query : $query->orderByDesc('gastos.id');
    }

    public function getEstadoReporteAttribute(): string
    {
        if ($this->estado === 'anulado') {
            return 'Anulado';
        }

        return $this->incluir_reporte_compras ? 'Pasado al reporte de compras' : 'Registrado';
    }
}
