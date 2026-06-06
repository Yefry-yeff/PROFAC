<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelBoletaCompra extends Model
{
    use HasFactory;

    protected $table = 'boleta_compra';
    protected $primaryKey = 'id';

    protected $fillable = [
        'numero_boleta',
        'cliente',
        'direccion',
        'rtn_dni',
        'telefono',
        'comentario',
        'fecha',
        'sub_total',
        'total',
        'estado',
        'cai_boleta_id',
        'users_id',
    ];
}
