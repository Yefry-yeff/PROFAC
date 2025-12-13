<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCotizacion extends Model
{
    use HasFactory;
    protected $table = 'cotizacion';

    protected $primaryKey = 'id';
}
