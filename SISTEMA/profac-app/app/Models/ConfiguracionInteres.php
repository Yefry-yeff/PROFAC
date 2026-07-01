<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionInteres extends Model
{
    protected $table = 'configuracion_intereses';

    protected $fillable = [
        'tasa_mensual',
        'estado',
        'fecha_vigencia',
        'fecha_fin_vigencia',
        'observaciones',
        'usr_creador',
        'usr_modificador',
        'empresa_id',
        'sucursal_id',
        'tipo_documento_id',
        'categoria_cliente_id',
    ];

    protected $casts = [
        'tasa_mensual'       => 'decimal:4',
        'estado'             => 'boolean',
        'fecha_vigencia'     => 'date',
        'fecha_fin_vigencia' => 'date',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function usuarioCreador()
    {
        return $this->belongsTo(\App\Models\User::class, 'usr_creador');
    }

    public function usuarioModificador()
    {
        return $this->belongsTo(\App\Models\User::class, 'usr_modificador');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    public function scopeVigentes($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        return $query->where('estado', true)
                     ->where('fecha_vigencia', '<=', $fecha)
                     ->orderByDesc('fecha_vigencia');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Devuelve la configuración vigente a una fecha dada.
     */
    public static function vigente(?string $fecha = null): ?self
    {
        return static::vigentes($fecha)->first();
    }
}
