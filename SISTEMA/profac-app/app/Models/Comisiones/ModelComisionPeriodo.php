<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Model;

class ModelComisionPeriodo extends Model
{
    protected $table      = 'comision_periodo';
    protected $primaryKey = 'id';

    protected $fillable = [
        'periodo',
        'estado',
        'total_comision',
        'total_comision_escala',
        'total_comision_politica_anterior',
        'total_comision_global',
        'cantidad_empleados',
        'cantidad_facturas',
        'observacion_conciliacion',
        'usuario_concilio',
        'fecha_conciliacion',
    ];

    protected $casts = [
        'periodo'             => 'date',
        'fecha_conciliacion'  => 'datetime',
        'total_comision'      => 'decimal:2',
        'total_comision_escala' => 'decimal:2',
        'total_comision_politica_anterior' => 'decimal:2',
        'total_comision_global' => 'decimal:2',
        'estado'              => 'integer',
    ];

    /* ── Constantes de estado ──────────────────────────────── */
    const ESTADO_ABIERTO     = 0;
    const ESTADO_CONCILIADO  = 1;

    /* ── Scopes ───────────────────────────────────────────── */
    public function scopeAbierto($query)
    {
        return $query->where('estado', self::ESTADO_ABIERTO);
    }

    public function scopeConciliado($query)
    {
        return $query->where('estado', self::ESTADO_CONCILIADO);
    }

    /* ── Helpers ──────────────────────────────────────────── */
    public function esConciliado(): bool
    {
        return (int) $this->estado === self::ESTADO_CONCILIADO;
    }

    public function esAbierto(): bool
    {
        return (int) $this->estado === self::ESTADO_ABIERTO;
    }
}
