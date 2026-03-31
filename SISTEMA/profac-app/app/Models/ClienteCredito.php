<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteCredito extends Model
{
    protected $table = 'cliente_credito';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cliente_id', 'credito_activo', 'credito', 'dias_credito',
        'vendedor_id', 'referencias_bancarias', 'referencias_comerciales',
        'metodo_pago', 'letra_cambio', 'aval_solidario',
        'autorizacion_gerencia', 'users_id',
    ];
}
