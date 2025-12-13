<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoRetencion extends Model
{
    use HasFactory;
    protected $table = 'tipo_retencion';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'nombre'];
}
