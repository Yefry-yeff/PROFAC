<?php

namespace App\Models\AplicacionPagos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelabonos_creditos extends Model
{
    use HasFactory;
    protected $table = 'abonos_creditos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aplicacion_pagos_id',
        'factura_id',
        'estado_abono',
        'monto_abonado',
        'usr_registro',
        'comentario',
        'url_documento',
        'fecha_pago'
    ];
}
