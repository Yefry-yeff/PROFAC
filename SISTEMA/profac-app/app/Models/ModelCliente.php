<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCliente extends Model
{
    use HasFactory;
    protected $table = 'cliente';
    protected $primaryKey = 'id';
   // protected $dateFormat = 'America/Tegucigalpa';
    protected $fillable = [
        'id',
        'rtn',  
        'correo',  
        'latitud',  
        'longitud',  
        'url_imagen',  
        'credito',  
        'tipo_cliente_id',  
        'tipo_personalidad_id',  
        'categoria_id',  
        'vendedor',  
        'users_id',  
        'estado_cliente_id',        
    ];


}
