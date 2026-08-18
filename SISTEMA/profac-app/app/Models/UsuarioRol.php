<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Roles ADICIONALES de un usuario (ademas de su rol principal en users.rol_id).
 * Ver comentario de la migracion 2026_07_24_000003_create_usuario_rol_table.
 */
class UsuarioRol extends Model
{
    use HasFactory;

    protected $table = 'usuario_rol';

    protected $fillable = [
        'usuario_id',
        'rol_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
