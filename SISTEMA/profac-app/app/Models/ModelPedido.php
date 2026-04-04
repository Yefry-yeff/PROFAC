<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelPedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cliente_id',
        'users_id',
        'estado',
        'observaciones',
    ];

    public function detalles()
    {
        return $this->hasMany(ModelPedidoDetalle::class, 'pedido_id');
    }

    public function cliente()
    {
        return $this->belongsTo(ModelCliente::class, 'cliente_id');
    }
}
