<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelTipoPagoCobro extends Model
{
    use HasFactory;
    protected $table = 'tipo_pago_cobro';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'descripcion'];
    
}
