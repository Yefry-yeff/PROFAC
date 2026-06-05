<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Model;

class ModelDiasGraciaComision extends Model
{
    protected $table    = 'dias_gracia_comision';
    protected $fillable = ['rol_id', 'tipo_factura', 'dias_gracia', 'descripcion', 'updated_by'];

    const TIPO_CONTADO = 'contado';
    const TIPO_CREDITO = 'credito';

    public function rol()
    {
        return $this->belongsTo(\App\Models\Role::class, 'rol_id');
    }
}
