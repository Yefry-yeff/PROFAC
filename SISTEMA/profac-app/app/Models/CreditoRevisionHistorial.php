<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: CreditoRevisionHistorial
 *
 * Auditoría inmutable de todos los eventos sobre una revisión de crédito.
 * Nunca se modifican registros — solo se insertan.
 */
class CreditoRevisionHistorial extends Model
{
    const UPDATED_AT = null; // tabla sin updated_at

    protected $table    = 'credito_revision_historial';
    protected $fillable = [
        'credito_revision_id',
        'flujo_id',
        'estado_anterior',
        'estado_nuevo',
        'accion',
        'descripcion',
        'usuario_id',
        'ip',
        'fecha_evento',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'created_at'   => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function revision()
    {
        return $this->belongsTo(CreditoRevision::class, 'credito_revision_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }
}
