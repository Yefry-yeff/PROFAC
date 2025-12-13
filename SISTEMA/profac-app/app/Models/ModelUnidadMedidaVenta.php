<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelUnidadMedidaVenta extends Model
{
    use HasFactory;
     
    protected $table = 'unidad_medida_venta';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'unidad_venta','unidad_medida_id','producto_id'];
}
