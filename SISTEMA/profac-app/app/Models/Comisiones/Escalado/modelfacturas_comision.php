<?php

namespace App\Models\Comisiones\Escalado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelfacturas_comision extends Model
{
    use HasFactory;
    protected $table = 'facturas_comision';
    protected $primaryKey = 'id';
    protected $fillable = [
    'fecha_cierre_factura',
    'monto_rol',
    'factura_id',
     'comision_escala_id',
     'aplicacion_pagos_id',
     'rol_id',
     'estado_id',
     'cantidad_usuariosxrol'
    ];
}
