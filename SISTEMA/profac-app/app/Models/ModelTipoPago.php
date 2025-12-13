<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelTipoPago extends Model
{
    use HasFactory;
    protected $table = 'tipo_compra';
    protected $fillable = ['id', 'descripcion'];
}
