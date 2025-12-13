<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelEstadoCliente extends Model
{
    use HasFactory;

    protected $table = 'estado_cliente';

    protected $primaryKey = 'id';

    protected $fillable = [
    'id',
    'descripcion',

    ];
}
