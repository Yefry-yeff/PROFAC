<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelLogEstadoCompra extends Model
{
    use HasFactory;
    protected $table = 'log_estado';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'compra_id',
        'estado_anterior_compra',
        'users_id'

    ];
}
