<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;

class DistribucionEntregaFactura extends Model
{
    protected $table = 'distribuciones_entrega_facturas';
    
    protected $fillable = [
        'distribucion_entrega_id',
        'factura_id',
        'orden_entrega',
        'estado_entrega',
        'fecha_entrega_real',
        'observaciones'
    ];

    protected $casts = [
        'fecha_entrega_real' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con la distribución
     */
    public function distribucion()
    {
        return $this->belongsTo(DistribucionEntrega::class, 'distribucion_entrega_id');
    }

    /**
     * Relación con la factura
     */
    public function factura()
    {
        return $this->belongsTo(\App\Models\ModelFactura::class, 'factura_id');
    }

    /**
     * Relación con los productos entregados
     */
    public function productosEntregados()
    {
        return $this->hasMany(EntregaProducto::class, 'distribucion_factura_id');
    }

    /**
     * Relación con las evidencias
     */
    public function evidencias()
    {
        return $this->hasMany(EntregaEvidencia::class, 'distribucion_factura_id');
    }

    /**
     * Scope para facturas sin entregar
     */
    public function scopeSinEntregar($query)
    {
        return $query->where('estado_entrega', 'sin_entrega');
    }

    /**
     * Scope para facturas parcialmente entregadas
     */
    public function scopeParciales($query)
    {
        return $query->where('estado_entrega', 'parcial');
    }

    /**
     * Scope para facturas entregadas
     */
    public function scopeEntregadas($query)
    {
        return $query->where('estado_entrega', 'entregado');
    }

    /**
     * Verificar si está completamente entregada
     */
    public function estaEntregada()
    {
        return $this->estado_entrega === 'entregado';
    }

    /**
     * Verificar si tiene entrega parcial
     */
    public function esParcial()
    {
        return $this->estado_entrega === 'parcial';
    }

    /**
     * Verificar si no tiene entregas
     */
    public function sinEntrega()
    {
        return $this->estado_entrega === 'sin_entrega';
    }
}
