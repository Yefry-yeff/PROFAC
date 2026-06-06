<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EquipoEntregaMiembro extends Model
{
    protected $table = 'equipos_entrega_miembros';
    
    protected $fillable = [
        'equipo_entrega_id',
        'user_id',
        'porcentaje_comision',
        'estado_id'
    ];

    protected $casts = [
        'porcentaje_comision' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el equipo de entrega
     */
    public function equipo()
    {
        return $this->belongsTo(EquipoEntrega::class, 'equipo_entrega_id');
    }

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para miembros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1);
    }

    /**
     * Verificar si el miembro está activo
     */
    public function estaActivo()
    {
        return $this->estado_id == 1;
    }
}
