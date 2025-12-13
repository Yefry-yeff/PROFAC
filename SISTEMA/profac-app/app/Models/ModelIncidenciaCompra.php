<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelIncidenciaCompra extends Model
{
    use HasFactory;  
    protected $primaryKey = 'id';
    protected $table = 'incidencia_compra';
    protected $fillable = [
        'id',
        'descripcion',
        'url_img',
        'compra_id',
        'producto_id',
        'users_id'

    ];
}
