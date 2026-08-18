<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Model;

class ModelComisionPeriodoLog extends Model
{
    protected $table      = 'comision_periodo_log';
    protected $primaryKey = 'id';

    protected $fillable = [
        'periodo',
        'comision_periodo_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'snapshot_total_comision',
        'snapshot_cantidad_empleados',
        'snapshot_cantidad_facturas',
        'snapshot_detalle_empleados',
        'snapshot_detalle_facturas',
        'observacion',
        'usuario_id',
        'usuario_nombre',
    ];

    protected $casts = [
        'periodo'                    => 'date',
        'snapshot_detalle_empleados' => 'array',
        'snapshot_detalle_facturas'  => 'array',
        'snapshot_total_comision'    => 'decimal:2',
        'estado_anterior'            => 'integer',
        'estado_nuevo'               => 'integer',
    ];

    /* ── Constantes de acción ──────────────────────────────── */
    const ACCION_CONCILIACION = 'conciliacion';
    const ACCION_REAPERTURA   = 'reapertura';
}
