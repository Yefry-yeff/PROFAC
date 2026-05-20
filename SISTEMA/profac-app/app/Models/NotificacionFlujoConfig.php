<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionFlujoConfig extends Model
{
    protected $table = 'notificacion_flujo_config';

    protected $fillable = [
        'tipo_tramite_id',
        'rol_id',
        'area_id',
        'nivel_max_id',
        'escalar_activo',
        'escalar_horas',
        'escalar_nivel_id',
        'activo',
    ];

    protected $casts = [
        'escalar_activo' => 'boolean',
        'activo'         => 'boolean',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function tipoTramite()
    {
        return $this->belongsTo(\stdClass::class, 'tipo_tramite_id'); // tabla tipos_tramites sin modelo propio
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function nivelMax()
    {
        return $this->belongsTo(NivelRol::class, 'nivel_max_id');
    }

    public function escalarNivel()
    {
        return $this->belongsTo(NivelRol::class, 'escalar_nivel_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeParaTramite($query, int $tipoTramiteId)
    {
        return $query->where('tipo_tramite_id', $tipoTramiteId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Retorna los IDs de usuarios que deben recibir notificación
     * según la regla (por rol directo o por área con filtro de nivel).
     */
    public function resolverUsuariosDestino(): \Illuminate\Support\Collection
    {
        $query = User::query()->where('estado_id', 1); // solo usuarios activos

        if ($this->rol_id) {
            // Targeting por rol específico
            $query->where('rol_id', $this->rol_id);
        } elseif ($this->area_id) {
            // Targeting por área: obtener rol_ids del área
            $rolesQuery = Rol::where('area_id', $this->area_id);

            if ($this->nivel_max_id) {
                $nivelMax = NivelRol::find($this->nivel_max_id);
                if ($nivelMax) {
                    // orden mayor = nivel más bajo (Colaborador=4, Supervisor=3...)
                    // nivel_max_id indica el nivel máximo (más bajo) a notificar
                    $rolesQuery->whereHas('nivel', function ($q) use ($nivelMax) {
                        $q->where('orden', '>=', $nivelMax->orden);
                    });
                }
            }

            $rolIds = $rolesQuery->pluck('id')->toArray();
            $query->whereIn('rol_id', $rolIds);
        } else {
            // Sin targeting definido: no notificar a nadie
            return collect();
        }

        return $query->get();
    }

    /**
     * Retorna los IDs de usuarios para escalar (nivel superior en la misma área).
     */
    public function resolverUsuariosEscalacion(): \Illuminate\Support\Collection
    {
        if (!$this->escalar_activo || !$this->escalar_nivel_id || !$this->area_id) {
            return collect();
        }

        $nivelEscalacion = NivelRol::find($this->escalar_nivel_id);
        if (!$nivelEscalacion) return collect();

        $rolIds = Rol::where('area_id', $this->area_id)
            ->whereHas('nivel', function ($q) use ($nivelEscalacion) {
                $q->where('orden', '<=', $nivelEscalacion->orden);
            })
            ->pluck('id')
            ->toArray();

        return User::where('estado_id', 1)
            ->whereIn('rol_id', $rolIds)
            ->get();
    }
}
