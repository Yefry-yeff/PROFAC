<?php

namespace App\Models\Comisiones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comision_techo extends Model
{
    use HasFactory;
    protected $table = 'comision_techo';
    protected $primaryKey = 'id';
    protected $fillable = ['monto_techo', 'mes', 'vendedor_id', 'estado_id'];
}
