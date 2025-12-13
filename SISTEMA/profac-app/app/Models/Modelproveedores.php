<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelproveedores extends Model
{
    use HasFactory;
    protected $table = 'proveedores';
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'contacto',
        'telefono_1',
        'telefono_2',
        'correo_1',
        'correo_2',
        'rtn',
        'pais',
        'departamento',
        'municipio',
        'registrado_por',
        'estado_id',
        'giro',
        'categoria',
        'retencion'
    ];
}
