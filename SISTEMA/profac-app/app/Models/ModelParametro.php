<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelParametro extends Model
{
    use HasFactory;
    protected $table = 'parametro';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'precio',
        'producto_id',
        'users_id'
        
    ];
}
