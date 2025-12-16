<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EntregaProductoIncidencia extends Model
{
    protected $table = 'entregas_productos_incidencias';

    protected $fillable = [
        'entrega_producto_id',
        'tipo',
        'descripcion',
        'user_id_registro',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(EntregaProducto::class, 'entrega_producto_id');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'user_id_registro');
    }

    public function evidencias()
    {
        return $this->hasMany(EntregaEvidencia::class, 'entrega_producto_incidencia_id');
    }
}
