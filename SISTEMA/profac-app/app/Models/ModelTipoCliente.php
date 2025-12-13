<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelTipoCliente extends Model
{
    use HasFactory;
    protected $table = 'tipo_cliente';

    protected $primaryKey = 'id';

    protected $fillable = [
    'id',
    'descripcion',

    ];
}
