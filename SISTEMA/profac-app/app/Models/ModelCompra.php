<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCompra extends Model
{
    use HasFactory;

    protected $table = 'compra';

    protected $primaryKey = 'id';

    protected $fillable = [
    'id',
    'fecha_vencimiento',
    'isv_compra', 'sub_total',      
    'total',
    'debito',
    'proveedores_id',
    'users_id',
    'tipo_compra_id',
    'numero_orden',
    'monto_retencion', 
    'retenciones_id', 
    'estado_compra_id',
    
    ];
}
