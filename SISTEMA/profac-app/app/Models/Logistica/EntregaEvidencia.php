<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EntregaEvidencia extends Model
{
    protected $table = 'entregas_evidencias';
    
    protected $fillable = [
        'distribucion_factura_id',
        'tipo_evidencia',
        'ruta_archivo',
        'descripcion',
        'user_id_registro'
    ];

    protected $casts = [
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
     * Relación con el usuario que registró
     */
    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'user_id_registro');
    }

    /**
     * Scope por tipo de evidencia
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_evidencia', $tipo);
    }

    /**
     * Obtener URL completa del archivo
     */
    public function getUrlArchivoAttribute()
    {
        return asset('storage/' . $this->ruta_archivo);
    }
}
