<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCategoria extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table = 'categoria_producto';
    protected $primaryKey = 'id';
    protected $fillable = [ 'descripcion'];
    
}
