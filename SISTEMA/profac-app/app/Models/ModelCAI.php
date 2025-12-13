<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCAI extends Model
{
    use HasFactory;
    protected $table = 'cai';
    protected $primaryKey = 'id';
    protected $fillable = [ 
    'id',    
    'cai', 
    'punto_de_emision',
    'cantidad_solicitada',
    'cantidad_otorgada',
    'serie',
    'numero_actual',
    'cantidad_no_utilizada',
    'numero_inicial',
    'numero_final',
    'numero_base',
    'fecha_limite_emision',
    'tipo_documento_fiscal_id',
    'estado_id',
    'users_id'
];
}
