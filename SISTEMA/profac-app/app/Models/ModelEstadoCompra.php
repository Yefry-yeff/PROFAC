<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelEstadoCompra extends Model
{
    use HasFactory;
    protected $table = 'estado_compra';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'estado',        
    ];
}
