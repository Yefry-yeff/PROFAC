<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetencionesProveedores extends Model
{
    use HasFactory;
    protected $table = 'retenciones_has_proveedores';
    protected $primaryKey = 'retenciones_id';
    protected $fillable = ['retenciones_id', 'proveedores_id'];


}
