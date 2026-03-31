<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteObservacion extends Model
{
    protected $table = 'cliente_observaciones';
    protected $primaryKey = 'id';

    protected $fillable = ['cliente_id', 'observacion', 'users_id'];
}
