<?php

namespace App\Models\Comisiones\Escalado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelcomision_escala extends Model
{
    use HasFactory;
    protected $table = 'comision_escala';
    protected $primaryKey = 'id';
    protected $fillable = ['estado_id', 'nombre', 'descripcion', 'cliente_categoria_escala_id','rol_id', 'rango_inicial','rango_final'];
}
