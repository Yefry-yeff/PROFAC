<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelTipoAjuste extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'tipo_ajuste';
    protected $fillable = ['id', 'nombre', 'users_id'];
}
