<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bitacoraCierre extends Model
{
    use HasFactory;

    protected $table = 'bitacoracierre';
    protected $primaryKey = 'id';
    protected $fillable = ['fechaCierre', 'user_cierre_id','comentario', 'estado_cierre','totalContado','totalCredito','totalAnulado'];
}
