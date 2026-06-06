<?php

namespace App\Models\Escalas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelCategoriaCliente extends Model
{
    use HasFactory;

    protected $table = 'cliente_categoria_escala';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre_categoria',
        'descripcion_categoria',
        'comentario_cat_cliente',
        'estado_id',
        'users_id_creador',
        'users_id_inactivo',
        'fecha_inactivacion',
        'created_at',
        'updated_at'
    ];

}
