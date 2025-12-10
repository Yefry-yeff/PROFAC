<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DistribucionEntregaMiembro extends Model
{
    protected $table = 'distribuciones_entrega_miembros';

    protected $fillable = [
        'distribucion_entrega_id',
        'user_id',
        'porcentaje_comision',
        'monto_comision_calculado',
        'pagado',
        'fecha_pago',
        'users_id_quien_pago',
    ];

    protected $casts = [
        'porcentaje_comision' => 'decimal:2',
        'monto_comision_calculado' => 'decimal:2',
        'pagado' => 'boolean',
        'fecha_pago' => 'datetime',
    ];

    /**
     * Relación: Pertenece a una distribución
     */
    public function distribucion()
    {
        return $this->belongsTo(DistribucionEntrega::class, 'distribucion_entrega_id');
    }

    /**
     * Relación: Usuario miembro del equipo
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Usuario que registró el pago
     */
    public function usuarioQuePago()
    {
        return $this->belongsTo(User::class, 'users_id_quien_pago');
    }

    /**
     * Scope: Comisiones pendientes de pago
     */
    public function scopePendientesPago($query)
    {
        return $query->where('pagado', 0);
    }

    /**
     * Scope: Comisiones ya pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('pagado', 1);
    }

    /**
     * Scope: Por usuario
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Marcar comisión como pagada
     */
    public function marcarComoPagada($userId = null)
    {
        $this->pagado = true;
        $this->fecha_pago = now();
        if ($userId) {
            $this->users_id_quien_pago = $userId;
        }
        return $this->save();
    }

    /**
     * Calcular monto de comisión basado en el total de la distribución
     */
    public function calcularComision($totalDistribucion)
    {
        $this->monto_comision_calculado = ($totalDistribucion * $this->porcentaje_comision) / 100;
        return $this->save();
    }
}
