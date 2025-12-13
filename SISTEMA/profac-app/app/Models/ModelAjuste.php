<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelAjuste extends Model
{
    use HasFactory;

    protected $table = 'ajuste';
    protected $primaryKey = 'id';
    protected $fillable = [ 
        'numero_ajuste',
        'comentario',
        'tipo_ajuste_id',
        'solicitado_por',
        'fecha',
        'users_id',     

    ];
    
}
