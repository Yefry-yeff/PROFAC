<?php

namespace App\Models\AplicacionPagos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelaplicacion_pagos extends Model
{
    use HasFactory;
    protected $table = 'aplicacion_pagos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'factura_id',
        'total_factura_cargo',
        'retencion_isv_factura',
        'estado_retencion_isv',
        'total_notas_credito',
        'total_nodas_debito',
        'credito_abonos',
        'comentario',
        'saldo',
        'ultimo_usr_actualizo',
        'estado',
        'estado_cerrado',
        'usr_cerro'
    ];

}
