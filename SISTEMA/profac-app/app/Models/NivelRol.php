<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelRol extends Model
{
    use HasFactory;

    protected $table = 'nivel_rol';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'estado_id',
    ];

    /**
     * Roles que tienen este nivel asignado.
     */
    public function roles()
    {
        return $this->hasMany(Rol::class, 'nivel_id');
    }

    /**
     * Scope: solo niveles activos, ordenados por jerarquía.
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1)->orderBy('orden');
    }
}
