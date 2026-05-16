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
        'nombre',
        'rtn',
        'correo',
        'latitud',
        'longitud',
        'url_imagen',
        'credito',
        'credito_inicial',
        'dias_credito',
        'telefono_empresa',
        'direccion',
        'municipio_id',
        'tipo_cliente_id',
        'tipo_personalidad_id',
        'categoria_id',
        'vendedor',
        'users_id',
        'estado_cliente_id',
        'cliente_categoria_escala_id',
        'categoria_precios_id',
        'ano_operacion',
        'dni_representante_legal',
        'metodo_pago',
        'ref_referencias',
        'ref_tiempo_relacion',
        'ref_tiempo_credito',
        'ref_limite_credito',
        'ref_observaciones',
    ];


}
