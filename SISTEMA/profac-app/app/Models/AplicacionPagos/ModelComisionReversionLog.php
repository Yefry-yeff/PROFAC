<?php

namespace App\Models\AplicacionPagos;

use Illuminate\Database\Eloquent\Model;

class ModelComisionReversionLog extends Model
{
    protected $table = 'comision_reversiones';

    protected $fillable = [
        'abono_id',
        'factura_id',
        'aplicacion_pagos_id',
        'monto_abono_anulado',
        'tenia_comisiones',
        'comisiones_revertidas',
        'motivo',
        'factura_reabierta',
        'usr_anulo',
    ];

    protected $casts = [
        'comisiones_revertidas' => 'array',
        'tenia_comisiones'       => 'boolean',
        'factura_reabierta'      => 'boolean',
    ];
}
