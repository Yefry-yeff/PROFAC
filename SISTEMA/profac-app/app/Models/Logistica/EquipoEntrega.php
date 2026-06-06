<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EquipoEntrega extends Model
{
    protected $table = 'equipos_entrega';
    
    protected $fillable = [
        'nombre_equipo',
        'descripcion',
        'estado_id',
        'users_id_creador'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'users_id_creador');
    }

    /**
     * Relación con los miembros del equipo
     */
    public function miembros()
    {
        return $this->hasMany(EquipoEntregaMiembro::class, 'equipo_entrega_id');
    }

    /**
     * Relación con los miembros activos del equipo
     */
    public function miembrosActivos()
    {
        return $this->hasMany(EquipoEntregaMiembro::class, 'equipo_entrega_id')
                    ->where('estado_id', 1);
    }

    /**
     * Relación con las distribuciones asignadas
     */
    public function distribuciones()
    {
        return $this->hasMany(DistribucionEntrega::class, 'equipo_entrega_id');
    }

    /**
     * Scope para equipos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1);
    }

    /**
     * Verificar si el equipo está activo
     */
    public function estaActivo()
    {
        return $this->estado_id == 1;
    }

    /**
     * Obtener la suma total de porcentajes de comisión
     */
    public function getTotalPorcentajesAttribute()
    {
        return $this->miembrosActivos()->sum('porcentaje_comision');
    }

    /**
     * Verificar si el equipo tiene cupo para más porcentaje
     */
    public function tieneCupoParaPorcentaje($porcentaje)
    {
        return ($this->total_porcentajes + $porcentaje) <= 100;
    }
}
