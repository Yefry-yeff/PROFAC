<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tipo_cobro_cierre extends Model
{
    use HasFactory;
    protected $table = 'tipo_cobro_cierre';
    protected $primaryKey = 'id';
    protected $fillable = ['textoCobro', 'fecha','factura','estado', 'user_registra_id'];
}
