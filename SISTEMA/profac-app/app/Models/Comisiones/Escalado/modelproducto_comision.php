<?php

namespace App\Models\Comisiones\Escalado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelproducto_comision extends Model
{
    use HasFactory;
    protected $table = 'producto_comision';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cantidad',
    'precio_venta',
    'descripcion',
     'monto_comision',
     'precios_producto_carga_id',
     'factura_id',
     'producto_id',
     'estado_id',
     'facturas_comision_id'
    ];
}
