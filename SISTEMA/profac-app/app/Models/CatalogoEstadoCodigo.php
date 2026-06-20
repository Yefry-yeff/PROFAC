<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoEstadoCodigo extends Model
{
    protected $table      = 'catalogo_estado_codigo';
    protected $primaryKey = 'id';
    public    $incrementing = true;
    protected $keyType    = 'int';

    protected $fillable = ['nombre', 'descripcion'];

    // Constantes para uso en código
    const PENDIENTE = 1;
    const UTILIZADO = 2;
    const EXPIRADO  = 3;
    const CANCELADO = 4;
}
