<?php

namespace App\Models\Escalas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class model_precios_producto_carga extends Model
{
    use HasFactory;

    protected $table = 'precios_producto_carga';
    protected $primaryKey = 'id';
    protected $fillable = [
        'categoria_precios_id',
        'comentario',
        'producto_id',
        'estado_id',
        'precio_a',
        'precio_b',
        'precio_c',
        'precio_d',
        'precio_base_venta',
        'tipo_categoria_precio_id',
        'users_id_creador',
        'precio_compra_usd',
        'tipo_cambio_usd',
        'precio_hnl',
        'flete',
        'arancel',
        'porc_flete',
        'porc_arancel',
        'categoria_producto_id',
        'sub_categoria_id',
        'marca_id',
        'unidad_medida_compra_id',
        'costoproducto'
    ];

}
