<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'area';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_id',
    ];

    /**
     * Roles que pertenecen a esta área.
     */
    public function roles()
    {
        return $this->hasMany(Rol::class, 'area_id');
    }

    /**
     * Scope: solo áreas activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('estado_id', 1)->orderBy('nombre');
    }
}
