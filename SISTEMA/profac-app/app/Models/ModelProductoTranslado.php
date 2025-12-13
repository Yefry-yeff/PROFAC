<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelProductoTranslado extends Model
{
    use HasFactory;

    protected $table = 'ajuste_has_producto';

    protected $fillable = [
        'ajuste_id',
        'producto_id',
        'recibido_bodega_id',
        'precio_producto',
        'cantidad_inicial',
        'cantidad',
        'cantidfad_total',
        'unidad_medida_venta_id',
        'created_at',
        'updated_at'
    ];
}
