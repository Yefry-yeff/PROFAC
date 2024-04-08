<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelVale extends Model
{
    use HasFactory;
    protected $table = 'vale';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'numero_vale','sub_total', 'isv','total', 'factura_id','users_id', 'notas','estado_id','porc_descuento','monto_descuento'];
}
