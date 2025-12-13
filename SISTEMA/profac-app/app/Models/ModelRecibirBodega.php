<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelRecibirBodega extends Model
{
    use HasFactory;
    protected $table = 'recibido_bodega';
    protected $primaryKey = 'id';
    protected $fillable = [
        'compraProducto_compra_id',
        'compraProducto_producto_id',
        'seccion_id',
        'cantidad_compra_lote',
        'cantidad_disponible',
        'fecha_recibido',
        'fecha_expiracion',
        'estado_recibido',
        'recibido_por', 
        'unidad_compra_id',
        'unidades_compra'      
    ];
}
