<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comision extends Model
{
    use HasFactory;
    protected $table = 'comision';
    protected $primaryKey = 'id';
    protected $fillable = ['comision_techo_id', 'factura_id', 'vendedor_id', 'gananciaTotal','porcentaje', 'monto_comison','estado_id', 'users_registro_id'];

}
