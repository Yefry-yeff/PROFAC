<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCodigoAutorizacion extends Model
{
    use HasFactory;
    protected $table = 'codigo_autorizacion';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'codigo','users_id','estado_id'];
}
