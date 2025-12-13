<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelVentaProducto extends Model
{
    use HasFactory;
    protected $table = 'venta_has_producto';
    protected $fillable = [
        'factura_id',
        'producto_id',
        'lote',
        'numero_unidades_resta_inventario',
        'unidad_medida_venta_id',
        'precio_unidad',
        'cantidad',
        'cantidad_para_entregar',
        'sub_total',
        'isv',
        'total',
        'seccion_id',
        'resta_inventario_total'
    ];
}
