<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelValeHasProducto extends Model
{
    use HasFactory;
    protected $table = 'vale_has_producto';
    protected $primaryKey = 'id';
    protected $fillable = [
        'vale_id',
        'producto_id',
        'lote_id',
        'seccion_id',
        'unidad_medida_venta_id',
        'cantidad',
        'precio_unidad', 
        'sub_total',
        'isv',
        'total', 
        'cantidad_para_entregar'

    ];
}
