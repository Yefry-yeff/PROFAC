<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelNumOrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'numero_orden_compra';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'numero_orden','cliente_id','estado_id','users_id'];

}
