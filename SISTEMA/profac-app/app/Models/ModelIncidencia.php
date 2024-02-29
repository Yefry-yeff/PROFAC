<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelIncidencia extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'incidencia';
    protected $fillable = [
        'descripcion',
        'url_img',
        'recibido_bodega_id',
        'users_id'       
    ];
}
