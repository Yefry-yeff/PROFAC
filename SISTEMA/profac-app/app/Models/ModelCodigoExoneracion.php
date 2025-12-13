<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCodigoExoneracion extends Model
{
    use HasFactory;

    protected $table = 'codigo_exoneracion';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'codigo','estado_id','corrOrd','cliente_id'];

}
