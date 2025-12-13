<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelUnidadMedida extends Model
{
    use HasFactory;
    
    protected $table = 'unidad_medida';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'unidad','nombre','simbolo'];
}
