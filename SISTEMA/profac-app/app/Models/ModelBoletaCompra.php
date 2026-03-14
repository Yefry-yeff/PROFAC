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
        'fecha',
        'sub_total',
        'total',
        'estado',
        'users_id',
    ];
}
