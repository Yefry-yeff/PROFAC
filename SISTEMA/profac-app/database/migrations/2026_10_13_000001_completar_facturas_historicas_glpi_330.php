<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('factura as f')
                ->leftJoin('factura_tratamiento_entrega as fte', 'fte.factura_id', '=', 'f.id')
                ->whereIn('f.estado_factura_id', [1, 2])
                ->where('f.estado_venta_id', 1)
                ->where('f.fecha_emision', '<', '2026-05-19')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('distribuciones_entrega_facturas as def')
                        ->whereColumn('def.factura_id', 'f.id')
                        ->where('def.estado_entrega', 'entregado');
                })
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('distribuciones_entrega_facturas as def')
                        ->join('distribuciones_entrega as de', 'de.id', '=', 'def.distribucion_entrega_id')
                        ->whereColumn('def.factura_id', 'f.id')
                        ->whereIn('de.estado_id', [1, 2])
                        ->where('def.estado_entrega', '!=', 'anulada');
                })
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('factura_tratamiento_entrega_historial as h')
                        ->whereColumn('h.factura_id', 'f.id')
                        ->where('h.estado', 'completada');
                })
                ->select('f.id', 'fte.department_id', 'fte.municipality_id', 'fte.direccion_entrega')
                ->selectRaw("
                    CASE
                        WHEN fte.factura_id IS NOT NULL THEN 'Tratada'
                        ELSE 'Pendiente'
                    END AS estado_anterior
                ")
                ->chunkById(500, function ($facturas) {
                    $fecha = now();
                    $registros = $facturas->map(fn ($factura) => [
                        'factura_id' => $factura->id,
                        'estado' => 'completada',
                        'distribucion_entrega_id' => null,
                        'department_id' => $factura->department_id,
                        'municipality_id' => $factura->municipality_id,
                        'direccion_entrega' => $factura->direccion_entrega,
                        'observaciones' => "Cambio de estado de {$factura->estado_anterior} a Completada, solicitado en GLPI-330",
                        'user_id' => null,
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ])->all();

                    DB::table('factura_tratamiento_entrega_historial')->insert($registros);
                }, 'f.id', 'id');
        });
    }

    public function down(): void
    {
        DB::table('factura_tratamiento_entrega_historial')
            ->where('estado', 'completada')
            ->whereNull('user_id')
            ->where('observaciones', 'like', 'Cambio de estado de % a Completada, solicitado en GLPI-330')
            ->delete();
    }
};
