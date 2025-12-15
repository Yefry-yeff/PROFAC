<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class usuario extends Model
{
    use HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'identidad','name','email', 'password', 'rol_id', 'estado_id'];

    /**
     * Relación con el estado del usuario
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    /**
     * Método para dar de baja (cambiar estado a Inactivo)
     */
    public function darDeBaja()
    {
        $this->estado_id = 2; // 2 = Inactivo
        return $this->save();
    }

    /**
     * Método para activar usuario
     */
    public function activar()
    {
        $this->estado_id = 1; // 1 = Activo
        return $this->save();
    }

    /**
     * Scope para filtrar solo usuarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1);
    }

    /**
     * Scope para filtrar solo usuarios inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado_id', 2);
    }
}
