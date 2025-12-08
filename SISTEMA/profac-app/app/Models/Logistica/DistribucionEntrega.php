<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DistribucionEntrega extends Model
{
    protected $table = 'distribuciones_entrega';
    
    protected $fillable = [
        'equipo_entrega_id',
        'fecha_programada',
        'observaciones',
        'estado_id',
        'users_id_creador'
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el equipo de entrega
     */
    public function equipo()
    {
        return $this->belongsTo(EquipoEntrega::class, 'equipo_entrega_id');
    }

    /**
     * Relación con el usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'users_id_creador');
    }

    /**
     * Relación con las facturas asignadas
     */
    public function facturas()
    {
        return $this->hasMany(DistribucionEntregaFactura::class, 'distribucion_entrega_id');
    }

    /**
     * Relación con las facturas sin entregar
     */
    public function facturasSinEntregar()
    {
        return $this->hasMany(DistribucionEntregaFactura::class, 'distribucion_entrega_id')
                    ->where('estado_entrega', 'sin_entrega');
    }

    /**
     * Relación con las facturas parcialmente entregadas
     */
    public function facturasParciales()
    {
        return $this->hasMany(DistribucionEntregaFactura::class, 'distribucion_entrega_id')
                    ->where('estado_entrega', 'parcial');
    }

    /**
     * Relación con las facturas entregadas
     */
    public function facturasEntregadas()
    {
        return $this->hasMany(DistribucionEntregaFactura::class, 'distribucion_entrega_id')
                    ->where('estado_entrega', 'entregado');
    }

    /**
     * Scope para distribuciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_id', 1);
    }

    /**
     * Scope para distribuciones en proceso
     */
    public function scopeEnProceso($query)
    {
        return $query->where('estado_id', 2);
    }

    /**
     * Scope para distribuciones completadas
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado_id', 3);
    }

    /**
     * Scope para distribuciones por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_programada', $fecha);
    }

    /**
     * Obtener el progreso de la distribución
     */
    public function getProgresoAttribute()
    {
        $total = $this->facturas()->count();
        if ($total == 0) return 0;
        
        $entregadas = $this->facturasEntregadas()->count();
        return round(($entregadas / $total) * 100, 2);
    }
}
