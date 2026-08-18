<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionCodigoAutorizacion extends Model
{
    protected $table   = 'configuracion_codigo_autorizacion';
    protected $fillable = ['tiempo_expiracion_minutos', 'expiracion_activa', 'actualizado_por'];

    protected $casts = [
        'expiracion_activa'         => 'boolean',
        'tiempo_expiracion_minutos' => 'integer',
    ];

    /**
     * Retorna la única fila de configuración (la crea si no existe).
     */
    public static function obtener(): self
    {
        return self::firstOrCreate([], [
            'tiempo_expiracion_minutos' => 10,
            'expiracion_activa'         => true,
        ]);
    }
}
