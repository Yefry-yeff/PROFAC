<?php

namespace App\Models\AplicacionPagos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelotros_movimientos extends Model
{
    use HasFactory;
    protected $table = 'otros_movimientos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aplicacion_pagos_id',
        'factura_id',
        'monto',
        'comentario',
        'url_documento',
        'usr_registro',
        'estado',
        'tipo_movimiento'
    ];
}
