<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelPrecio extends Model
{
    use HasFactory;
    protected $table = 'precios_venta';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'precio',
        'producto_id',
        'users_id'
        
    ];
}
