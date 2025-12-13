<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCategoriaProducto extends Model
{
    use HasFactory;
    protected $table = 'categoria_producto';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'descripcion'];
}
