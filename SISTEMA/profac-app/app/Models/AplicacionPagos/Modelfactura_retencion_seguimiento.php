<?php

namespace App\Models\AplicacionPagos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelfactura_retencion_seguimiento extends Model
{
    use HasFactory;

    protected $table = 'factura_retencion_seguimiento';

    protected $fillable = [
        'factura_id',
        'aplicacion_pagos_id',
        'cliente_id',
        'estado',
        'observacion_marcado',
        'observacion_resolucion',
        'usr_marcado',
        'usr_resolvio',
        'fecha_marcado',
        'fecha_resolucion',
        'numero_retencion',
        'archivo_retencion',
    ];
}
