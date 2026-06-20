<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCodigoAutorizacion extends Model
{
    use HasFactory;

    protected $table      = 'codigo_autorizacion';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'codigo',
        'users_id',
        'estado_id',
        'flujo_id',
        'tipo_tramite',
        'fecha_expiracion',
        'fecha_utilizacion',
        'estado_codigo_id',
    ];

    protected $casts = [
        'fecha_expiracion'  => 'datetime',
        'fecha_utilizacion' => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function estadoCodigo()
    {
        return $this->belongsTo(CatalogoEstadoCodigo::class, 'estado_codigo_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Retorna true si el código sigue siendo válido para el flujo/trámite indicados.
     * Marca como Expirado automáticamente si venció.
     */
    public function esValido(?int $flujoId = null, ?string $tipoTramite = null): bool
    {
        // Ya fue consumido o cancelado
        if ($this->estado_codigo_id !== CatalogoEstadoCodigo::PENDIENTE) {
            return false;
        }

        // Verificar expiración
        if ($this->fecha_expiracion && now()->greaterThan($this->fecha_expiracion)) {
            $this->update(['estado_codigo_id' => CatalogoEstadoCodigo::EXPIRADO]);
            return false;
        }

        // Validar flujo (si se especifica)
        if ($flujoId !== null && $this->flujo_id !== null && (int) $this->flujo_id !== $flujoId) {
            return false;
        }

        // Validar trámite (si se especifica)
        if ($tipoTramite !== null && $this->tipo_tramite !== null && $this->tipo_tramite !== $tipoTramite) {
            return false;
        }

        return true;
    }

    /**
     * Marca el código como utilizado.
     */
    public function marcarUtilizado(): void
    {
        $this->update([
            'estado_id'        => 2,
            'estado_codigo_id' => CatalogoEstadoCodigo::UTILIZADO,
            'fecha_utilizacion' => now(),
        ]);
    }
}

