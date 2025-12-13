<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class facturas_pagadas extends Model
{
    use HasFactory;
    protected $table = 'facturas_pagadas';
    protected $primaryKey = 'id';
    protected $fillable =  ['factura_id',
            'comision_id',
            'estado_pagado'];
}
