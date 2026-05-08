<?php

namespace App\Models\Escalas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelCategoriaPrecios extends Model
{
    use HasFactory;

    protected $table = 'categoria_precios';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre',
        'comentario',
        'estado_id',
        'users_id_registro',
        'fecha_inactivacion',
        'cliente_categoria_escala_id',
        'porc_precio_a',
        'porc_precio_b',
        'porc_precio_c',
        'porc_precio_d'
    ];

}
