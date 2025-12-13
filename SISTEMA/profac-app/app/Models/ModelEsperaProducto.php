<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelEsperaProducto extends Model
{
    use HasFactory;
    protected $table = 'espera_has_producto';



    protected $fillable = [
    'vale_id',
    'producto_id',
    'index',
    'cantidad',
    'cantidad_pendiente',
    'precio',
    'unidad_medida_venta_id',
    'sub_total',
    'isv',
    'total',
    'resta_inventario_total',
    'created_at',
    'updated_at'

    ];
}
