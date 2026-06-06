<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelOfertaProducto extends Model
{
    use HasFactory;

    protected $table = 'oferta_has_producto';

    protected $fillable = [
        'oferta_id',
        'producto_id',
        'indice',
        'nombre_producto',
        'nombre_bodega',
        'precio_unidad',
        'tipo_precio',
        'cantidad',
        'sub_total',
        'isv',
        'total',
        'bodega_id',
        'seccion_id',
        'resta_inventario',
        'isv_producto',
        'unidad_medida_venta_id',
        'monto_descProducto',
        'idPrecioSeleccionado',
        'precioSeleccionado',
        'precios_producto_carga_id',
    ];
}
