<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelpagoVenta extends Model
{
    use HasFactory;
    protected $table = 'pago_venta';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'monto',
        'fecha',
        'users_id',
        'factura_id',
        'estado_venta_id',
        'fecha_eliminado',
        'users_id_elimina',
        'ulr_img',
        'created_at',
        'updated_at'        
    ];
}
