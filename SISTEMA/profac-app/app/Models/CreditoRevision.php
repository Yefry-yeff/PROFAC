<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: CreditoRevision
 *
 * Gestiona el estado de revisión crediticia para un flujo dado.
 * Estados posibles (constantes): PENDIENTE, APROBADO, RECHAZADO.
 */
class CreditoRevision extends Model
{
    // ── Constantes de estado ──────────────────────────────────────────────
    const PENDIENTE  = 'pendiente';
    const APROBADO   = 'aprobado';
    const RECHAZADO  = 'rechazado';
    const CANCELADO  = 'cancelado';

    protected $table    = 'credito_revision';
    protected $fillable = [
        'flujo_id',
        'cotizacion_id',
        'estado',
        'fecha_aprobacion',
        'fecha_vencimiento_credito',
        'dias_credito_aprobados',
        'motivo_rechazo',
        'observaciones',
        'usuario_revision',
        'ip_revision',
    ];

    protected $casts = [
        'fecha_aprobacion'          => 'date',
        'fecha_vencimiento_credito' => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function historial()
    {
        return $this->hasMany(CreditoRevisionHistorial::class, 'credito_revision_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Busca el registro de crédito activo para un flujo dado.
     * "Activo" = el más reciente que no esté rechazado.
     */
    public static function paraFlujo(int $flujoId): ?self
    {
        return static::where('flujo_id', $flujoId)
                     ->latest('id')
                     ->first();
    }

    /**
     * Verifica si para el flujo dado ya existe una aprobación vigente.
     * Condición: estado = 'aprobado' Y fecha_vencimiento_credito >= hoy (o NULL = sin vencimiento).
     */
    public static function creditoVigenteParaFlujo(int $flujoId): bool
    {
        $registro = static::where('flujo_id', $flujoId)
                          ->where('estado', self::APROBADO)
                          ->latest('id')
                          ->first();

        if (!$registro) {
            return false;
        }

        // Si no tiene fecha de vencimiento definida, se considera vigente indefinidamente
        if (!$registro->fecha_vencimiento_credito) {
            return true;
        }

        return now()->startOfDay()->lte(
            \Carbon\Carbon::parse($registro->fecha_vencimiento_credito)->startOfDay()
        );
    }

    /**
     * Registra un evento en el historial de este registro.
     */
    public function registrarHistorial(
        string  $accion,
        ?string $estadoAnterior,
        string  $estadoNuevo,
        ?string $descripcion = null,
        ?string $ip          = null
    ): void {
        CreditoRevisionHistorial::create([
            'credito_revision_id' => $this->id,
            'flujo_id'            => $this->flujo_id,
            'estado_anterior'     => $estadoAnterior,
            'estado_nuevo'        => $estadoNuevo,
            'accion'              => $accion,
            'descripcion'         => $descripcion,
            'usuario_id'          => Auth::id(),
            'ip'                  => $ip ?? request()->ip(),
            'fecha_evento'        => now(),
        ]);
    }
}
