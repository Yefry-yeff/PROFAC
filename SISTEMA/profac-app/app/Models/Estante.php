<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estante extends Model
{
    use HasFactory;

    protected $table = 'estante';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'nombre', 'bodega_id'];
}
