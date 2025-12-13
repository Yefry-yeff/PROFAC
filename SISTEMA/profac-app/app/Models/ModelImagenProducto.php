<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelImagenProducto extends Model
{
    use HasFactory;
    protected $table = 'img_producto';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'url_img',
        'producto_id',
        'users_id'
        
    ];
}
