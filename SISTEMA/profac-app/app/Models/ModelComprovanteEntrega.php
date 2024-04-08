<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelComprovanteEntrega extends Model
{
    use HasFactory;
    protected $table = 'comprovante_entrega';

    protected $primaryKey = 'id';
    protected $fillable = [
        'porc_descuento',
        'monto_descuento'
    ];     
}
