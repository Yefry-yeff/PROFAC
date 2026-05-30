<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FlujoEtapa extends Model
{
    protected $table    = 'flujo_etapas';
    protected $keyType  = 'int';

    protected $fillable = [
        'tipo_tramite_id',
        'nombre_display',
        'icono',
        'orden',
        'es_opcional',
        'activo',
        'updated_by',
    ];

    protected $casts = [
        'es_opcional' => 'boolean',
        'activo'      => 'boolean',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('activo', 1)->orderBy('orden');
    }

    // ── Relación ─────────────────────────────────────────────────────────────

    public function tipoTramite()
    {
        return $this->belongsTo(\Illuminate\Support\Facades\DB::table('tipos_tramites'), 'tipo_tramite_id');
    }

    // ── Helper estático ──────────────────────────────────────────────────────

    /**
     * Retorna las etapas activas ordenadas, cacheadas 1 hora.
     * Úsalo en cualquier lugar donde antes se leía tipos_tramites directamente.
     *
     * @return \Illuminate\Support\Collection  [ {id, tipo_tramite_id, nombre_display, icono, orden, es_opcional} ]
     */
    public static function activas(): \Illuminate\Support\Collection
    {
        return Cache::remember('flujo_etapas_activas', 3600, function () {
            return static::where('activo', 1)
                ->orderBy('orden')
                ->get(['id', 'tipo_tramite_id', 'nombre_display', 'icono', 'orden', 'es_opcional']);
        });
    }

    /**
     * Invalida el cache cuando se modifica el catálogo.
     */
    public static function limpiarCache(): void
    {
        Cache::forget('flujo_etapas_activas');
    }

    protected static function booted(): void
    {
        static::saved(fn()   => static::limpiarCache());
        static::deleted(fn() => static::limpiarCache());
    }
}
