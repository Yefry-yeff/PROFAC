<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaInteres extends Model
{
    protected $table = 'factura_interes';

    protected $fillable = [
        'factura_id',
        'configuracion_interes_id',
        'fecha_inicio',
        'fecha_fin',
        'capital_base',
        'porcentaje_aplicado',
        'dias_vencidos',
        'monto_interes',
        'estado',
        'cobrado',
        'fecha_cobro',
        'usuario_cobro',
        'usr_no_cobro',
        'fecha_no_cobro',
        'motivo_no_cobro',
        'anulado',
        'fecha_anulacion',
        'usuario_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'capital_base'        => 'decimal:2',
        'porcentaje_aplicado' => 'decimal:4',
        'monto_interes'       => 'decimal:2',
        'cobrado'             => 'boolean',
        'anulado'             => 'boolean',
        'fecha_inicio'        => 'date',
        'fecha_fin'           => 'date',
        'fecha_cobro'         => 'date',
        'fecha_no_cobro'      => 'datetime',
        'fecha_anulacion'     => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function configuracion()
    {
        return $this->belongsTo(ConfiguracionInteres::class, 'configuracion_interes_id');
    }

    public function usuarioCobro()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_cobro');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_anulacion');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('estado', 1)->where('anulado', false);
    }

    public function scopeCobrados($query)
    {
        return $query->activos()->where('cobrado', true);
    }

    public function scopePendientes($query)
    {
        return $query->activos()->where('cobrado', false);
    }

    public function scopePorFactura($query, int $facturaId)
    {
        return $query->where('factura_id', $facturaId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Devuelve el registro de interés activo y no cobrado para una factura.
     * Retorna null si no existe (el cálculo es dinámico hasta que se persiste).
     */
    public static function pendientePorFactura(int $facturaId): ?self
    {
        return static::porFactura($facturaId)->pendientes()->latest()->first();
    }

    /**
     * Verifica si ya existe un interés cobrado para evitar duplicados.
     */
    public static function yaCobrado(int $facturaId): bool
    {
        return static::porFactura($facturaId)->cobrados()->exists();
    }
}
