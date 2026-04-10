<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelPedidoDetalle extends Model
{
    protected $table = 'pedido_detalle';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pedido_id',
        'nombre_producto',
        'cantidad',
    ];
}
