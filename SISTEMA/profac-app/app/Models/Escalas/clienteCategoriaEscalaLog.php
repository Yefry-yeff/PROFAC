<?php

namespace App\Models\Escalas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clienteCategoriaEscalaLog extends Model
{
    protected $table = 'cliente_categoria_escala_logs';

    // Campos reales entabla:
    protected $fillable = [
        'id',
        'antigua_categoria',
        'nueva_categoria',
        'comentario',
        'users_id',
        'cliente_id'
    ];
}
