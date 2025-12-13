<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class desglose extends Model
{
    use HasFactory;
    protected $table = 'desglose';
    protected $primaryKey = 'id';
    protected $fillable =  ['idFactura',
            'numero_factura',
            'idProducto',
            'producto',
           'precio_base',
            'ultimo_costo_compra',
            'unidad_venta',
            'cantidad',
            'precio_unidad',
            'gananciaUnidad',
            'gananciatotal',
            'total',
            'sub_total',
            'isv',
            'seccion_id',
            'seccion',
             'bodega',
             'vendedor_id',
             'estadoComisionado'];
}
