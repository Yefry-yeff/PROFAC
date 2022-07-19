<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCotizacionProducto extends Model
{
    use HasFactory;
    protected $table = 'cotizacion_has_producto';    

    protected $fillable = [
    'cotizacion_id',
    'producto_id',
    'nombre_bodega',
    'precio_unidad',
    'cantidad',
    'sub_total',
    'isv',
    'total',
    'seccion_id',
    'unidad_medida_venta_id',
   
    ];
}
