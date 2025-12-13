<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelPagoCompra extends Model
{
    use HasFactory;
    protected $table = 'pago_compra';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'monto',
        'fecha',
        'users_id',
        'compra_id',
        'estado_id',
        'fecha_eliminado',
        'users_id_elimina',
        'created_at',
        'updated_at'        
    ];
}
