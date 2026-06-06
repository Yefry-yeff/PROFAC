<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Logistica\EntregaProductoIncidencia;

class EntregaProducto extends Model
{
    protected $table = 'entregas_productos';
    
    protected $fillable = [
        'distribucion_factura_id',
        'producto_id',
        'cantidad_facturada',
        'cantidad_entregada',
        'entregado',
        'tiene_incidencia',
        'descripcion_incidencia',
        'tipo_incidencia',
        'user_id_registro',
        'fecha_registro'
    ];

    protected $casts = [
        'cantidad_facturada' => 'decimal:2',
        'cantidad_entregada' => 'decimal:2',
        'entregado' => 'boolean',
        'tiene_incidencia' => 'boolean',
        'fecha_registro' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con la distribución de factura
     */
    public function distribucionFactura()
    {
        return $this->belongsTo(DistribucionEntregaFactura::class, 'distribucion_factura_id');
    }

    /**
     * Relación con el producto
     */
    public function producto()
    {
        return $this->belongsTo(\App\Models\ModelProducto::class, 'producto_id');
    }

    /**
     * Relación con el usuario que registró
     */
    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'user_id_registro');
    }

    /**
     * Incidencias registradas para este producto entregado
     */
    public function incidencias()
    {
        return $this->hasMany(EntregaProductoIncidencia::class, 'entrega_producto_id');
    }

    /**
     * Scope para productos entregados
     */
    public function scopeEntregados($query)
    {
        return $query->where('entregado', 1);
    }

    /**
     * Scope para productos no entregados
     */
    public function scopeNoEntregados($query)
    {
        return $query->where('entregado', 0);
    }

    /**
     * Scope para productos con incidencia
     */
    public function scopeConIncidencia($query)
    {
        return $query->where('tiene_incidencia', 1);
    }

    /**
     * Marcar como entregado
     */
    public function marcarComoEntregado($cantidad = null, $userId = null)
    {
        $this->entregado = true;
        $this->cantidad_entregada = $cantidad ?? $this->cantidad_facturada;
        $this->user_id_registro = $userId ?? auth()->id();
        $this->fecha_registro = now();
        $this->save();
    }

    /**
     * Registrar incidencia
     */
    public function registrarIncidencia($tipo, $descripcion, $userId = null)
    {
        $this->tiene_incidencia = true;
        $this->tipo_incidencia = $tipo;
        $this->descripcion_incidencia = $descripcion;
        $this->user_id_registro = $userId ?? auth()->id();
        $this->fecha_registro = now();
        $this->save();
    }
}
