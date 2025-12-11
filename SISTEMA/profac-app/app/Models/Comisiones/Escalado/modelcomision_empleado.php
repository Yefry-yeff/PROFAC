<?php

namespace App\Models\Comisiones\Escalado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelcomision_empleado extends Model
{
    use HasFactory;
    protected $table = 'comision_empleado';
    protected $primaryKey = 'id';
    protected $fillable = [
    'comision_acumulada',
    'fecha_ult_modificacion',
    'mes_comision',
    'nombre_empleado',
    'users_comision',
    'rol_id',
    'estado_id'
    ];
}
