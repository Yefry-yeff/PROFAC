<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelProducto extends Model
{
    use HasFactory;
    protected $table = 'producto';
    protected $primaryKey = 'id';
    protected $fillable = [
        'url_img',
        'nombre',
        'descripcion',
        'ivs',
        'codigo_barra',
        'codigo_estatal',
        'categoria_id',
        'unidad_medida_compra_id',
        'marca_id',
        'ultimo_costo_compra'
    ];
}
