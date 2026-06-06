<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelBoletaCompraDetalle extends Model
{
    use HasFactory;

    protected $table = 'boleta_compra_detalle';
    protected $primaryKey = 'id';

    protected $fillable = [
        'boleta_compra_id',
        'linea',
        'descripcion',
        'precio',
        'cantidad',
        'importe',
    ];
}
