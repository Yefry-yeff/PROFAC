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
        'banco_id',
        'id_tipo_pago_cobro',
        'estado_abono',
        'monto_abonado',
        'usr_registro',
        'comentario',
        'url_documento',
        'fecha_pago',
        'numero_recibo',
        'periodo_comision_original',
        'periodo_comision_asignado',
        'desvio_confirmado_por',
    ];
}
