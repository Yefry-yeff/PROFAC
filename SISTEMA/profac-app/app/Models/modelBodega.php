<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modelBodega extends Model
{
    use HasFactory;
    protected $table = 'bodega';
    protected $primaryKey = 'id';
    protected $fillable = [ 'nombre', 'direccion','users_id','estado_id'];
}
