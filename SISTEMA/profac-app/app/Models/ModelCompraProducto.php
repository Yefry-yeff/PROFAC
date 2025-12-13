<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCompraProducto extends Model
{
    protected $table = 'compra_has_producto';

    // protected $primaryKey = ['compra_id','producto_id'];

    protected $fillable = ['fecha_recibido','compra_id','producto_id','precio_unidad_compra', 'isv', 'precio_total', 'cantidad_ingresada','cantidad_disponible', 'fecha_expiracion', ];
}
