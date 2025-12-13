<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelNotaCreditoProducto extends Model
{
    use HasFactory;
    protected $table = 'nota_credito_has_producto';

    protected $fillable = [
        'nota_credito_id',
        'producto_id',
        'cantidad',
        'precio_unidad',
        'sub_total',
        'isv',
        'total',
        'seccion_id',
        'unidad_medida_venta_id'

    ];
}
