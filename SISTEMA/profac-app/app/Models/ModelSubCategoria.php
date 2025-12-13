<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelSubCategoria extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table = 'sub_categoria';
    protected $primaryKey = 'id';
    protected $fillable = [ 'descripcion', 'categoria_producto_id'];

}
