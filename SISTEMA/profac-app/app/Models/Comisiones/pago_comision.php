<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pago_comision extends Model
{
    use HasFactory;
    protected $table = 'pago_comision';
    protected $primaryKey = 'id';
    protected $fillable =  ['vendedor_id',
            'nombre_vendedor',
            'mes_comision',
            'cantidad_facturas',
            'techo_asignado',
            'ganancia_total',
            'monto_asignado',
            'estado_pago',
            'url_comprobante',
        'users_registra_id'];
}
