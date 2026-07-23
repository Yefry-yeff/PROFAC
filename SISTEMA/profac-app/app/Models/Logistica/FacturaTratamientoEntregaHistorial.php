<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;

class FacturaTratamientoEntregaHistorial extends Model
{
    protected $table = 'factura_tratamiento_entrega_historial';

    protected $fillable = [
        'factura_id',
        'estado',
        'distribucion_entrega_id',
        'department_id',
        'municipality_id',
        'direccion_entrega',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
