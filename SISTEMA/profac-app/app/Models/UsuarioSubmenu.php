<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioSubmenu extends Model
{
    protected $table = 'usuario_submenu';

    protected $fillable = [
        'usuario_id',
        'sub_menu_id',
        'permitido',
    ];

    protected $casts = [
        'permitido' => 'boolean',
    ];
}
