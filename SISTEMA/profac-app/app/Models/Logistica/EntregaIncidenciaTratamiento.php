<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EntregaIncidenciaTratamiento extends Model
{
    protected $table = 'entregas_incidencias_tratamientos';

    protected $fillable = [
        'entrega_producto_incidencia_id',
        'tratamiento',
        'user_id_registro',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function incidencia()
    {
        return $this->belongsTo(EntregaProductoIncidencia::class, 'entrega_producto_incidencia_id');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'user_id_registro');
    }
}
