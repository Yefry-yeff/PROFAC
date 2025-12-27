<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelLogTranslados extends Model
{
    use HasFactory;
    protected $table = 'log_translado';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'origen',
        'destino',
        'cantidad',
        'comprovante_entrega_id',
        'users_id',
        'unidad_medida_venta_id',
        'nota_credito_id',
        'descripcion',
        'translado_id'

    ];
}
