<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteDireccion extends Model
{
    protected $table = 'cliente_direccion';

    protected $fillable = [
        'cliente_id',
        'etiqueta',
        'pais_id',
        'departamento_id',
        'municipio_id',
        'direccion',
        'latitud',
        'longitud',
        'principal',
        'activo',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'activo' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(ModelCliente::class, 'cliente_id');
    }
}
