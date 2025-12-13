<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelComprovanteHasProducto extends Model
{
    use HasFactory;
    protected $table = 'comprovante_has_producto';
    protected $fillable = [
        'comprovante_id',
        'producto_id',      
        'lote_id',
        'seccion_id',
        'numero_unidades_resta_inventario',
        'resta_inventario_total',
        'unidad_medida_venta_id',
        'precio_unidad',
        'cantidad',
        'cantidad_s',
        'cantidad_para_entregar',
        'sub_total',
        'isv',
        'total',
        'sub_total_s',
        'isv_s',
        'total_s',
       

        ];
  
}
