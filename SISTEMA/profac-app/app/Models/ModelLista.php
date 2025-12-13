<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelLista extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'listado';
    protected $fillable = [
        'numero',
        'secuencia',     

    ];
}
