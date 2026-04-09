<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoFactura extends Model
{
    use HasFactory;

    protected $table = 'tipo_factura';

    protected $fillable = [
        'nombre',
        'codigo',
        'ruta_menu',
        'tipo_venta_id',
        'restriccion',
        'max_descuento',
        'requiere_codigo_autorizacion',
        'requiere_codigo_exoneracion',
        'requiere_orden_compra',
        'aplica_isv',
        'multiples_precios',
        'comision_fija',
        'estado',
        'orden',
    ];

    protected $casts = [
        'requiere_codigo_autorizacion' => 'boolean',
        'requiere_codigo_exoneracion' => 'boolean',
        'requiere_orden_compra' => 'boolean',
        'aplica_isv' => 'boolean',
        'multiples_precios' => 'boolean',
        'estado' => 'boolean',
    ];

    public function facturas()
    {
        return $this->hasMany(ModelFactura::class, 'tipo_factura_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', true)->orderBy('orden');
    }
}
