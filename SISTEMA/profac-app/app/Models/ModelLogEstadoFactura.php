<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelLogEstadoFactura extends Model
{
    use HasFactory;
    protected $table = 'log_estado_factura';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'motivo',
        'factura_id',
        'estado_venta_id_anterior',
        'users_id',
        'created_at',
        'updated_at'

    ];
}
